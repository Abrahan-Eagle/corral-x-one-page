<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use App\Models\Conversation;
use App\Models\Message;
use App\Events\MessageSent;
use App\Events\TypingStarted;
use App\Events\TypingStopped;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends BaseController
{
    public function getConversations()
    {
        $profileId = optional(Auth::user()->profile)->id;
        
        $conversations = Conversation::query()
            ->where('profile_id_1', $profileId)
            ->orWhere('profile_id_2', $profileId)
            ->with(['participant1', 'participant2', 'messages' => function($query) {
                $query->orderBy('created_at', 'desc')->limit(1);
            }])
            ->orderByDesc('last_message_at')
            ->get()
            ->map(function($conversation) use ($profileId) {
                // Obtener el otro participante
                $otherParticipant = $conversation->getOtherParticipant($profileId);
                
                // Obtener el último mensaje
                $lastMessage = $conversation->getLastMessage();
                
                // Contar mensajes no leídos
                $unreadCount = $conversation->getUnreadMessagesCount($profileId);
                
                return [
                    'id' => $conversation->id,
                    'profile_id_1' => $conversation->profile_id_1,
                    'profile_id_2' => $conversation->profile_id_2,
                    'product_id' => $conversation->product_id,
                    'ranch_id' => $conversation->ranch_id,
                    'last_message' => $lastMessage ? $lastMessage->content : null,
                    'last_message_at' => $lastMessage ? $lastMessage->created_at : $conversation->last_message_at,
                    'unread_count' => $unreadCount,
                    'is_active' => $conversation->is_active,
                    'created_at' => $conversation->created_at,
                    'updated_at' => $conversation->updated_at,
                    'other_participant' => $otherParticipant ? [
                        'id' => $otherParticipant->id,
                        'name' => $otherParticipant->firstName . ' ' . $otherParticipant->lastName,
                        'avatar' => $otherParticipant->photo_users,
                        'is_online' => false, // TODO: Implementar estado online
                        'is_verified' => $otherParticipant->is_verified,
                    ] : null,
                ];
            });
            
        return response()->json($conversations);
    }

    public function getMessages($conversationId)
    {
        $user = Auth::user();
        $profileId = optional($user->profile)->id;
        
        // Logging detallado para diagnóstico en producción
        \Log::info('🔍 ChatController@getMessages - Diagnóstico', [
            'user_id' => $user?->id,
            'profile_id' => $profileId,
            'has_profile' => $user && $user->profile ? 'YES' : 'NO',
            'conversation_id' => $conversationId,
            'auth_guard' => Auth::getDefaultDriver(),
        ]);
        
        if (!$profileId) {
            \Log::warning('❌ Usuario sin perfil intentó acceder a mensajes', [
                'user_id' => $user?->id,
                'conversation_id' => $conversationId,
            ]);
            abort(403, 'No tienes un perfil asociado. Completa el onboarding primero.');
        }
        
        $conversation = Conversation::findOrFail($conversationId);
        
        if (!$conversation->hasParticipant($profileId)) {
            \Log::warning('❌ Usuario no es participante de la conversación', [
                'user_id' => $user->id,
                'profile_id' => $profileId,
                'profile_id_type' => gettype($profileId),
                'conversation_id' => $conversationId,
                'conversation_profile_1' => $conversation->profile_id_1,
                'conversation_profile_1_type' => gettype($conversation->profile_id_1),
                'conversation_profile_2' => $conversation->profile_id_2,
                'conversation_profile_2_type' => gettype($conversation->profile_id_2),
                'comparison_1' => (int)$conversation->profile_id_1 === (int)$profileId ? 'MATCH' : 'NO_MATCH',
                'comparison_2' => (int)$conversation->profile_id_2 === (int)$profileId ? 'MATCH' : 'NO_MATCH',
            ]);
            abort(403, 'No participas en esta conversación.');
        }
        
        $messages = $conversation->messages()->orderBy('created_at')->get();
        return response()->json($messages);
    }

    public function sendMessage(Request $request, $conversationId)
    {
        $request->validate(['content' => ['required','string','max:2000']]);
        
        $user = Auth::user();
        $senderId = optional($user->profile)->id;
        
        // Logging detallado para diagnóstico en producción
        \Log::info('🔍 ChatController@sendMessage - Diagnóstico', [
            'user_id' => $user?->id,
            'profile_id' => $senderId,
            'has_profile' => $user && $user->profile ? 'YES' : 'NO',
            'conversation_id' => $conversationId,
        ]);
        
        if (!$senderId) {
            \Log::warning('❌ Usuario sin perfil intentó enviar mensaje', [
                'user_id' => $user?->id,
                'conversation_id' => $conversationId,
            ]);
            abort(403, 'No tienes un perfil asociado. Completa el onboarding primero.');
        }
        
        $conversation = Conversation::findOrFail($conversationId);
        
        if (!$conversation->hasParticipant($senderId)) {
            \Log::warning('❌ Usuario no es participante de la conversación', [
                'user_id' => $user->id,
                'profile_id' => $senderId,
                'conversation_id' => $conversationId,
                'conversation_profile_1' => $conversation->profile_id_1,
                'conversation_profile_2' => $conversation->profile_id_2,
            ]);
            abort(403, 'No participas en esta conversación.');
        }
        
        // Crear el mensaje
        $message = $conversation->messages()->create([
            'sender_id' => $senderId,
            'content' => $request->string('content'),
            'message_type' => 'text',
        ]);
        
        // Actualizar última actividad de la conversación
        $conversation->update(['last_message_at' => now()]);
        
        // Cargar relación sender (Profile) para broadcast y respuesta
        $message->load('sender');
        
        // Broadcast del evento MessageSent via Pusher
        broadcast(new MessageSent($message, (int)$conversationId))->toOthers();
        
        // 🔔 Enviar notificación push al receptor SOLO si la app del receptor está en background/cerrada
        // Regla simple: si el receptor tiene un token FCM, se envía push; en foreground la app la maneja localmente y Pusher ya entrega el mensaje
        $this->sendPushNotification($conversation, $message, $senderId);
        
        \Log::info('✅ Mensaje enviado y broadcast realizado', [
            'message_id' => $message->id,
            'conversation_id' => $conversationId,
            'sender_id' => $senderId
        ]);
        
        return response()->json($message, 201);
    }

    public function markMessagesAsRead($conversationId)
    {
        $user = Auth::user();
        $profileId = optional($user->profile)->id;
        
        // Logging detallado para diagnóstico en producción
        \Log::info('🔍 ChatController@markMessagesAsRead - Diagnóstico', [
            'user_id' => $user?->id,
            'profile_id' => $profileId,
            'has_profile' => $user && $user->profile ? 'YES' : 'NO',
            'conversation_id' => $conversationId,
        ]);
        
        if (!$profileId) {
            \Log::warning('❌ Usuario sin perfil intentó marcar mensajes como leídos', [
                'user_id' => $user?->id,
                'conversation_id' => $conversationId,
            ]);
            abort(403, 'No tienes un perfil asociado. Completa el onboarding primero.');
        }
        
        $conversation = Conversation::findOrFail($conversationId);
        
        if (!$conversation->hasParticipant($profileId)) {
            \Log::warning('❌ Usuario no es participante de la conversación', [
                'user_id' => $user->id,
                'profile_id' => $profileId,
                'profile_id_type' => gettype($profileId),
                'conversation_id' => $conversationId,
                'conversation_profile_1' => $conversation->profile_id_1,
                'conversation_profile_1_type' => gettype($conversation->profile_id_1),
                'conversation_profile_2' => $conversation->profile_id_2,
                'conversation_profile_2_type' => gettype($conversation->profile_id_2),
                'comparison_1' => (int)$conversation->profile_id_1 === (int)$profileId ? 'MATCH' : 'NO_MATCH',
                'comparison_2' => (int)$conversation->profile_id_2 === (int)$profileId ? 'MATCH' : 'NO_MATCH',
            ]);
            abort(403, 'No participas en esta conversación.');
        }
        
        Message::where('conversation_id', $conversation->id)
            ->whereNull('read_at')
            ->where('sender_id', '!=', $profileId)
            ->update(['read_at' => now()]);
        return response()->json(['marked' => true]);
    }

    public function createConversation(Request $request)
    {
        $request->validate([
            'profile_id_2' => ['required','exists:profiles,id'],
            'product_id' => ['nullable','exists:products,id'],
            'ranch_id' => ['nullable','exists:ranches,id'],
        ]);
        $profileId1 = optional(Auth::user()->profile)->id;
        $profileId2 = (int) $request->input('profile_id_2');
        $productId = $request->has('product_id') ? (int) $request->input('product_id') : null;
        $ranchId = $request->has('ranch_id') ? (int) $request->input('ranch_id') : null;
        
        // ✅ Validar que no sea el mismo usuario
        if ($profileId1 === $profileId2) {
            return response()->json([
                'error' => 'No puedes crear una conversación contigo mismo'
            ], 422);
        }
        
        // Asegurar que profile_id_1 sea el menor para evitar duplicados
        $profileId1Final = min($profileId1, $profileId2);
        $profileId2Final = max($profileId1, $profileId2);
        
        $conv = Conversation::firstOrCreate([
            'profile_id_1' => $profileId1Final,
            'profile_id_2' => $profileId2Final,
            'product_id' => $productId,
            'ranch_id' => $ranchId,
        ], [
            'last_message_at' => now(),
            'is_active' => true,
        ]);
        
        // Cargar relaciones para respuesta completa
        $conv->load(['participant1', 'participant2', 'product', 'ranch']);
        
        return response()->json($conv, 201);
    }

    public function deleteConversation($conversationId)
    {
        $conversation = Conversation::findOrFail($conversationId);
        $profileId = optional(Auth::user()->profile)->id;
        
        if (!$profileId || !$conversation->hasParticipant($profileId)) {
            abort(403, 'No participas en esta conversación.');
        }
        
        $conversation->delete();
        return response()->json(['deleted' => true]);
    }

    public function searchMessages(Request $request)
    {
        $term = $request->string('q');
        $profileId = optional(Auth::user()->profile)->id;
        $messages = Message::query()
            ->where('content', 'like', "%{$term}%")
            ->whereHas('conversation', function ($q) use ($profileId) {
                $q->where('profile_id_1', $profileId)->orWhere('profile_id_2', $profileId);
            })
            ->limit(50)
            ->get();
        return response()->json($messages);
    }

    public function blockUser(Request $request)
    {
        // Stub de ejemplo: dependerá de modelo/bd para bloqueos. Devuelve 200 OK.
        return response()->json(['blocked' => true]);
    }

    public function unblockUser($userId)
    {
        // Stub de ejemplo: dependerá de modelo/bd para bloqueos. Devuelve 200 OK.
        return response()->json(['unblocked' => true]);
    }

    public function getBlockedUsers()
    {
        // Stub de ejemplo: retornar lista vacía por ahora
        return response()->json([]);
    }
    
    /**
     * Notificar que el usuario está escribiendo
     */
    public function typingStarted(Request $request, $conversationId)
    {
        $conversation = Conversation::findOrFail($conversationId);
        $user = Auth::user();
        $profile = $user->profile;
        
        if (!$profile || !$conversation->hasParticipant($profile->id)) {
            abort(403, 'No participas en esta conversación.');
        }
        
        // Obtener el nombre del usuario (firstName + lastName)
        $userName = $profile->firstName . ' ' . $profile->lastName;
        
        // Broadcast del evento de typing via Pusher
        broadcast(new TypingStarted(
            $profile->id,
            $userName,
            (int)$conversationId
        ))->toOthers();
        
        return response()->json(['status' => 'typing_started']);
    }
    
    /**
     * Notificar que el usuario dejó de escribir
     */
    public function typingStopped(Request $request, $conversationId)
    {
        $conversation = Conversation::findOrFail($conversationId);
        $profile = Auth::user()->profile;
        
        if (!$profile || !$conversation->hasParticipant($profile->id)) {
            abort(403, 'No participas en esta conversación.');
        }
        
        // Broadcast del evento de typing stopped via Pusher
        broadcast(new TypingStopped(
            $profile->id,
            (int)$conversationId
        ))->toOthers();
        
        return response()->json(['status' => 'typing_stopped']);
    }

    /**
     * Registrar device token de Firebase FCM para notificaciones push
     */
    public function registerFcmToken(Request $request)
    {
        $request->validate([
            'device_token' => 'required|string'
        ]);

        $user = Auth::user();
        $profile = $user->profile;

        // Manejar caso donde el usuario aún no tiene perfil creado
        if (!$profile) {
            \Log::warning('⚠️ Intento de registrar FCM token sin perfil asociado', [
                'user_id' => $user?->id,
                'device_token_preview' => substr($request->device_token, 0, 20) . '...',
            ]);

            // No lanzar 500: simplemente informar al frontend para que pueda reintentar
            return response()->json([
                'status' => 'profile_missing',
                'message' => 'Token recibido pero el usuario aún no tiene perfil asociado.',
            ], 200);
        }

        // Guardar token en el perfil (evitar problemas de fillable)
        $profile->fcm_device_token = $request->device_token;
        $profile->save();

        \Log::info('✅ FCM token registrado', [
            'profile_id' => $profile->id,
            'token' => substr($request->device_token, 0, 20) . '...'
        ]);

        return response()->json(['status' => 'token_registered']);
    }

    /**
     * Eliminar device token de Firebase FCM
     */
    public function unregisterFcmToken(Request $request)
    {
        $profile = Auth::user()->profile;

        if (!$profile) {
            \Log::warning('⚠️ Intento de eliminar FCM token sin perfil asociado', [
                'user_id' => Auth::id(),
            ]);

            return response()->json(['status' => 'token_unregistered']);
        }

        $profile->update([
            'fcm_device_token' => null
        ]);

        \Log::info('✅ FCM token eliminado', ['profile_id' => $profile->id]);

        return response()->json(['status' => 'token_unregistered']);
    }

    /**
     * Enviar notificación push al receptor del mensaje
     */
    private function sendPushNotification($conversation, $message, $senderId)
    {
        \Log::info('🚀 INICIO sendPushNotification', [
            'conversation_id' => $conversation->id,
            'message_id' => $message->id,
            'sender_id' => $senderId
        ]);
        
        try {
            // Obtener el receptor (el otro participante)
            $receiver = $conversation->getOtherParticipant($senderId);
            
            \Log::info('🔍 Debug sendPushNotification', [
                'sender_id' => $senderId,
                'receiver' => $receiver ? $receiver->id : 'null',
                'has_fcm_token' => $receiver && $receiver->fcm_device_token ? 'YES' : 'NO',
                'token_preview' => $receiver && $receiver->fcm_device_token ? substr($receiver->fcm_device_token, 0, 20) . '...' : 'null'
            ]);
            
            if (!$receiver || !$receiver->fcm_device_token) {
                \Log::info('⚠️ Receptor sin device token, no se envía push', [
                    'receiver_id' => $receiver ? $receiver->id : 'null',
                    'has_token' => $receiver && $receiver->fcm_device_token ? 'YES' : 'NO'
                ]);
                return;
            }

            // Obtener nombre del remitente (sender ya es un Profile)
            $sender = $message->sender;
            $senderName = $sender->firstName . ' ' . $sender->lastName;
            
            // Preparar snippet del mensaje (máximo 100 caracteres para notificación)
            $content = (string)$message->content; // Convertir a string si es Stringable
            $snippet = strlen($content) > 100 
                ? substr($content, 0, 97) . '...' 
                : $content;
            
            // Enviar notificación con datos estilo WhatsApp
            \Log::info('🔥 LLAMANDO FirebaseService->sendToDevice', [
                'device_token' => substr($receiver->fcm_device_token, 0, 20) . '...',
                'title' => $senderName,
                'body' => $snippet
            ]);
            
            $firebaseService = new FirebaseService();
            $result = $firebaseService->sendToDevice(
                $receiver->fcm_device_token,
                $senderName, // Título: nombre del remitente
                $snippet,    // Cuerpo: snippet del mensaje
                [
                    'conversation_id' => (string)$conversation->id,
                    'message_id' => (string)$message->id,
                    'sender_id' => (string)$senderId,
                    'sender_name' => $senderName,
                    'snippet' => $snippet,
                    'full_message' => (string)$message->content,
                    'type' => 'chat_message',
                    'timestamp' => $message->created_at->timestamp,
                ]
            );
            
            \Log::info('🔥 RESULTADO FirebaseService->sendToDevice', [
                'result' => $result ? 'SUCCESS' : 'FAILED'
            ]);

            \Log::info('📬 Notificación push enviada', [
                'receiver_id' => $receiver->id,
                'sender_name' => $senderName,
                'conversation_id' => $conversation->id
            ]);
        } catch (\Exception $e) {
            \Log::error('❌ Error enviando notificación push', [
                'error' => $e->getMessage(),
                'conversation_id' => $conversation->id
            ]);
        }
    }
}
