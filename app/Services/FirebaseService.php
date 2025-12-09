<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

/**
 * Servicio para enviar notificaciones push via Firebase Cloud Messaging
 */
class FirebaseService
{
    protected $messaging;

    public function __construct()
    {
        try {
            // Obtener ruta de credenciales desde configuración (permite usar variable de entorno)
            // El valor por defecto se usa solo si FIREBASE_CREDENTIALS no está definido en .env
            $credentialsPath = config('services.firebase.credentials', 'storage/app/corralx-777-aipp-firebase-adminsdk-fbsvc-7d6a9eda94.json');
            
            // Si la ruta es relativa (no empieza con /), asumir que es relativa a storage_path
            if (substr($credentialsPath, 0, 1) !== '/') {
                // Remover 'storage/app/' si está presente en la ruta
                $credentialsPath = str_replace('storage/app/', '', $credentialsPath);
                $credentialsPath = storage_path('app/' . $credentialsPath);
            }
            
            // Verificar que el archivo existe
            if (!file_exists($credentialsPath)) {
                \Log::error('❌ Archivo de credenciales de Firebase no encontrado', [
                    'path' => $credentialsPath,
                    'config' => config('services.firebase.credentials')
                ]);
                $this->messaging = null;
                return;
            }
            
            $factory = (new Factory)->withServiceAccount($credentialsPath);
            $this->messaging = $factory->createMessaging();
            
            \Log::info('✅ Firebase Service inicializado', [
                'credentials_path' => $credentialsPath,
                'project_id' => $this->getProjectId($credentialsPath)
            ]);
        } catch (\Exception $e) {
            \Log::error('❌ Error inicializando Firebase', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->messaging = null;
        }
    }
    
    /**
     * Obtener project_id del archivo de credenciales (para logging)
     */
    private function getProjectId(string $credentialsPath): string
    {
        try {
            $credentials = json_decode(file_get_contents($credentialsPath), true);
            return $credentials['project_id'] ?? 'unknown';
        } catch (\Exception $e) {
            return 'unknown';
        }
    }

    /**
     * Enviar notificación push a un device token específico
     */
    public function sendToDevice(string $deviceToken, string $title, string $body, array $data = [])
    {
        if (!$this->messaging) {
            \Log::warning('⚠️ Firebase messaging no disponible');
            return false;
        }

        try {
            // Crear notificación para mostrar en background/foreground
            $notification = Notification::create($title, $body);
            
            // Crear mensaje con notificación y datos adicionales
            // Enviar notificación + datos para que funcione en foreground y background
            $message = CloudMessage::withTarget('token', $deviceToken)
                ->withNotification($notification)
                ->withData($data);

            $this->messaging->send($message);

            \Log::info('✅ Notificación push enviada', [
                'title' => $title,
                'body' => substr($body, 0, 50) . '...',
                'device_token' => substr($deviceToken, 0, 20) . '...',
                'conversation_id' => $data['conversation_id'] ?? 'N/A',
                'message_id' => $data['message_id'] ?? 'N/A',
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('❌ Error enviando notificación push', [
                'error' => $e->getMessage(),
                'device_token' => substr($deviceToken, 0, 20) . '...',
                'title' => $title,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Enviar notificación a múltiples dispositivos
     */
    public function sendToMultipleDevices(array $deviceTokens, string $title, string $body, array $data = [])
    {
        if (!$this->messaging) {
            return false;
        }

        $successCount = 0;
        foreach ($deviceTokens as $token) {
            if ($this->sendToDevice($token, $title, $body, $data)) {
                $successCount++;
            }
        }

        \Log::info("✅ Notificaciones enviadas: {$successCount}/{count($deviceTokens)}");
        return $successCount;
    }
}

