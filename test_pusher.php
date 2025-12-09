<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🧪 TEST 1: VERIFICAR CONFIGURACIÓN DE PUSHER\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "BROADCAST_DRIVER: " . env('BROADCAST_DRIVER') . "\n";
echo "PUSHER_APP_ID: " . env('PUSHER_APP_ID') . "\n";
echo "PUSHER_APP_KEY: " . env('PUSHER_APP_KEY') . "\n";
echo "PUSHER_APP_CLUSTER: " . env('PUSHER_APP_CLUSTER') . "\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🧪 TEST 2: ENVIAR EVENTO DE PRUEBA A PUSHER\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

try {
    // Crear evento simple
    $pusher = new Pusher\Pusher(
        env('PUSHER_APP_KEY'),
        env('PUSHER_APP_SECRET'),
        env('PUSHER_APP_ID'),
        [
            'cluster' => env('PUSHER_APP_CLUSTER'),
            'useTLS' => true
        ]
    );
    
    echo "✅ Cliente Pusher creado\n\n";
    
    // Enviar mensaje de prueba al canal público
    $data = [
        'message' => [
            'id' => 9999,
            'content' => 'Test desde PHP',
            'user_id' => 1,
            'created_at' => now()->toISOString()
        ]
    ];
    
    $result = $pusher->trigger('conversation.678', 'MessageSent', $data);
    
    if ($result) {
        echo "✅ Evento enviado exitosamente a Pusher!\n";
        echo "   Canal: conversation.678\n";
        echo "   Evento: MessageSent\n";
        echo "   Datos: " . json_encode($data, JSON_PRETTY_PRINT) . "\n\n";
    } else {
        echo "❌ Error enviando evento\n\n";
    }
    
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "🧪 TEST 3: VERIFICAR CONEXIÓN CON PUSHER API\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    // Obtener info de canales
    $channels = $pusher->get('/channels');
    
    if ($channels) {
        echo "✅ Conexión exitosa con Pusher API\n";
        echo "Canales activos:\n";
        $channelsData = json_decode($channels, true);
        if (isset($channelsData['channels'])) {
            foreach ($channelsData['channels'] as $channel => $info) {
                echo "  - $channel\n";
            }
        } else {
            echo "  (ningún canal activo)\n";
        }
    } else {
        echo "❌ Error conectando con Pusher API\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ TEST COMPLETADO\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

