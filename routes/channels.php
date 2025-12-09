<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/*
|--------------------------------------------------------------------------
| Chat Channels (CorralX MVP)
|--------------------------------------------------------------------------
|
| Canales privados para chat 1:1. Solo los participantes de una
| conversación pueden suscribirse y escuchar mensajes.
|
*/

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    // 🔍 Logging de autenticación
    \Log::info('🔍 Broadcasting auth request', [
        'conversation_id' => $conversationId,
        'user_id' => $user ? $user->id : 'NULL',
        'socket_id' => request()->input('socket_id', 'NULL'),
    ]);
    
    // Verificar que el usuario está autenticado
    if (!$user) {
        \Log::warning('❌ Usuario no autenticado intentó acceder', [
            'conversation_id' => $conversationId
        ]);
        return false;
    }
    
    // Verificar que el usuario tiene un perfil
    $profileId = optional($user->profile)->id;
    
    if (!$profileId) {
        \Log::warning('❌ Usuario sin profile intentó acceder a conversación', [
            'user_id' => optional($user)->id,
            'conversation_id' => $conversationId
        ]);
        return false;
    }
    
    $conversation = \App\Models\Conversation::find($conversationId);
    
    if (!$conversation) {
        \Log::warning('❌ Conversación no encontrada', [
            'conversation_id' => $conversationId
        ]);
        return false;
    }
    
    // El usuario puede acceder si es uno de los dos participantes
    // Usar comparación no estricta para manejar String vs Int desde la BD
    $isParticipant = (int)$conversation->profile_id_1 === (int)$profileId 
                  || (int)$conversation->profile_id_2 === (int)$profileId;
    
    if ($isParticipant) {
        \Log::info('✅ Usuario autorizado para canal privado', [
            'user_id' => $user->id,
            'profile_id' => $profileId,
            'profile_id_type' => gettype($profileId),
            'conversation_id' => $conversationId,
            'conversation_profile_1' => $conversation->profile_id_1,
            'conversation_profile_1_type' => gettype($conversation->profile_id_1),
            'conversation_profile_2' => $conversation->profile_id_2,
            'conversation_profile_2_type' => gettype($conversation->profile_id_2),
        ]);
        
        return [
            'id' => $profileId,
            'name' => optional($user->profile)->first_name . ' ' . optional($user->profile)->last_name
        ];
    }
    
    \Log::warning('❌ Usuario NO autorizado para canal', [
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
    
    return false;
});
