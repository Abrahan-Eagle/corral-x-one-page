<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Authenticator\AuthController;
use App\Http\Controllers\Profiles\ProfileController;
use App\Http\Controllers\Profiles\AddressController;
use App\Http\Controllers\Profiles\PhoneController;
use App\Http\Controllers\Profiles\RanchController;
use App\Http\Controllers\Profiles\KycController;
use App\Http\Controllers\Marketplace\AdvertisementController;
use App\Http\Controllers\Insights\IAInsightsController;
use App\Http\Controllers\Orders\OrderController;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;


Route::prefix('auth')->group(function () {
    Route::post('/google', [AuthController::class, 'googleUser']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'getUser']);
        Route::put('/user', [AuthController::class, 'updateProfile']);
        Route::put('/password', [AuthController::class, 'changePassword']);
        Route::post('/refresh', [AuthController::class, 'refreshToken']);
        Route::delete('/account', [AuthController::class, 'deleteAccount']);
    });
});


// Rutas para usuarios/buyers
Route::middleware(['auth:sanctum'])->group(function () {
    
    
    Route::prefix('phones')->group(function () {
        Route::get('/', [PhoneController::class, 'index']);
        Route::get('/operator-codes', [PhoneController::class, 'getOperatorCodes']);
        Route::post('/', [PhoneController::class, 'store']);
        Route::get('/{id}', [PhoneController::class, 'show']);
        Route::put('/{id}', [PhoneController::class, 'update']);
        Route::delete('/{id}', [PhoneController::class, 'destroy']);
    });

});

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {

    // Usuario autenticado
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });

    Route::prefix('onboarding')->group(function () {
        Route::put('/{id}', [AuthController::class, 'update']);
    });

    // Perfil del usuario autenticado
    Route::get('/profile', [ProfileController::class, 'getMyProfile']);
    Route::put('/profile', [ProfileController::class, 'updateMyProfile']);
    Route::post('/profile/photo', [ProfileController::class, 'uploadPhoto']);
    
    // KYC básico (MVP)
    Route::prefix('kyc')->group(function () {
        Route::get('/status', [KycController::class, 'status']);
        Route::post('/start', [KycController::class, 'start']);
        Route::post('/upload-document', [KycController::class, 'uploadDocument']);
        Route::post('/upload-selfie', [KycController::class, 'uploadSelfie']);
        Route::post('/upload-liveness-selfies', [KycController::class, 'uploadLivenessSelfies']);
        Route::post('/upload-selfie-with-doc', [KycController::class, 'uploadSelfieWithDoc']);
        Route::post('/extract-document-data', [KycController::class, 'extractDocumentDataWithGemini']);
    });
    
    // Verificar completitud del perfil y hacienda para publicar
    Route::get('/me/completeness', [ProfileController::class, 'checkCompleteness']);
    
    // Obtener perfil de otro usuario por ID (para chat)
    Route::get('/profile/{id}', [ProfileController::class, 'show']);

     Route::prefix('profiles')->group(function () {
        Route::get('/', [ProfileController::class, 'index']);
        Route::post('/', [ProfileController::class, 'store']);
        Route::post('/delivery-agent', [ProfileController::class, 'createDeliveryAgent']);
        Route::post('/commerce', [ProfileController::class, 'createCommerce']);
        Route::post('/delivery-company', [ProfileController::class, 'createDeliveryCompany']);
        Route::get('/{id}', [ProfileController::class, 'show']);
        Route::get('/{id}/ranches', [RanchController::class, 'getByProfile']);
        Route::post('/{id}', [ProfileController::class, 'update']);
        Route::delete('/{id}', [ProfileController::class, 'destroy']);
    });


    // Grupo de documentos eliminado (datos de CI/RIF ahora van en profiles/ranches)


    Route::prefix('addresses')->group(function () {
        Route::get('/', [AddressController::class, 'index']);
        Route::post('/', [AddressController::class, 'store']);
        Route::get('/{id}', [AddressController::class, 'show']);
        Route::put('/{id}', [AddressController::class, 'update']);
        Route::delete('/{id}', [AddressController::class, 'destroy']);
        Route::post('/getCountries', [AddressController::class, 'getCountries']);
        Route::post('/get-states-by-country', [AddressController::class, 'getState']);
        Route::post('/get-cities-by-state', [AddressController::class, 'getCity']);
        Route::post('/get-parishes-by-city', [AddressController::class, 'getParishes']);
    });


    // Chat routes (MVP)
    Route::prefix('chat')->group(function () {
        Route::get('/conversations', [\App\Http\Controllers\ChatController::class, 'getConversations']);
        Route::get('/conversations/{conversationId}/messages', [\App\Http\Controllers\ChatController::class, 'getMessages']);
        Route::post('/conversations/{conversationId}/messages', [\App\Http\Controllers\ChatController::class, 'sendMessage']);
        Route::post('/conversations/{conversationId}/read', [\App\Http\Controllers\ChatController::class, 'markMessagesAsRead']);
        Route::post('/conversations', [\App\Http\Controllers\ChatController::class, 'createConversation']);
        Route::delete('/conversations/{conversationId}', [\App\Http\Controllers\ChatController::class, 'deleteConversation']);
        Route::get('/search', [\App\Http\Controllers\ChatController::class, 'searchMessages']);
        Route::post('/block', [\App\Http\Controllers\ChatController::class, 'blockUser']);
        Route::delete('/block/{userId}', [\App\Http\Controllers\ChatController::class, 'unblockUser']);
        Route::get('/blocked-users', [\App\Http\Controllers\ChatController::class, 'getBlockedUsers']);
        
        // WebSocket typing indicators
        Route::post('/conversations/{conversationId}/typing/start', [\App\Http\Controllers\ChatController::class, 'typingStarted']);
        Route::post('/conversations/{conversationId}/typing/stop', [\App\Http\Controllers\ChatController::class, 'typingStopped']);
    });

    // Firebase FCM device token registration
    Route::post('/fcm/register-token', [\App\Http\Controllers\ChatController::class, 'registerFcmToken']);
    Route::delete('/fcm/unregister-token', [\App\Http\Controllers\ChatController::class, 'unregisterFcmToken']);

    // Favorites routes (MVP)
    Route::get('/me/favorites', [\App\Http\Controllers\FavoriteController::class, 'index']);
    Route::post('/products/{product}/favorite', [\App\Http\Controllers\FavoriteController::class, 'toggle']);
    Route::get('/products/{product}/is-favorite', [\App\Http\Controllers\FavoriteController::class, 'check']);
    Route::delete('/products/{product}/favorite', [\App\Http\Controllers\FavoriteController::class, 'destroy']);
    Route::get('/products/{product}/favorites-count', [\App\Http\Controllers\FavoriteController::class, 'count']);

        Route::prefix('ia-insights')->group(function () {
            Route::get('/dashboard', [IAInsightsController::class, 'dashboard']);
            Route::post('/recommendations/{key}/status', [IAInsightsController::class, 'updateRecommendationStatus']);
            Route::post('/users/{user}/level', [IAInsightsController::class, 'updateUserLevel']);
        });
});

// =============================================================
// MVP: Productos (marketplace de ganado)
// - Público: index/show
// - Protegido: store/update/destroy
// =============================================================
// Público
Route::get('/products', [\App\Http\Controllers\Marketplace\ProductController::class, 'index']);
Route::get('/products/{product}', [\App\Http\Controllers\Marketplace\ProductController::class, 'show']);

// Ranchos públicos (listar todos)
Route::get('/ranches', [RanchController::class, 'index']);
Route::get('/ranches/{ranch}', [RanchController::class, 'show']);
Route::get('/ranches/{ranch}/products', [RanchController::class, 'getProducts']);

// =============================================================
// MVP: Anuncios/Publicidad (marketplace)
// - Público: active (anuncios activos)
// - Admin: CRUD completo
// =============================================================
// Público: Obtener anuncios activos (para marketplace)
Route::get('/advertisements/active', [AdvertisementController::class, 'active']);

// Público: Registrar click en anuncio (tracking)
Route::post('/advertisements/{advertisement}/click', [AdvertisementController::class, 'click']);

// Protegido: CRUD completo (solo admin)
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('advertisements')->group(function () {
        Route::get('/', [AdvertisementController::class, 'index']); // Listar todos (admin)
        Route::post('/', [AdvertisementController::class, 'store']); // Crear (admin)
        Route::get('/{advertisement}', [AdvertisementController::class, 'show']); // Ver detalle (admin)
        Route::put('/{advertisement}', [AdvertisementController::class, 'update']); // Actualizar (admin)
        Route::delete('/{advertisement}', [AdvertisementController::class, 'destroy']); // Eliminar (admin)
    });
});

// Protegido
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/products', [\App\Http\Controllers\Marketplace\ProductController::class, 'store']);
    Route::post('/products/{product}/images', [\App\Http\Controllers\Marketplace\ProductController::class, 'uploadImages']);
    Route::put('/products/{product}', [\App\Http\Controllers\Marketplace\ProductController::class, 'update']);
    Route::delete('/products/{product}', [\App\Http\Controllers\Marketplace\ProductController::class, 'destroy']);
    
    // Endpoints para onboarding
    Route::prefix('addresses')->group(function () {
        Route::get('/', [AddressController::class, 'index']);
        Route::post('/', [AddressController::class, 'store']);
        Route::get('/{id}', [AddressController::class, 'show']);
        Route::put('/{id}', [AddressController::class, 'update']);
        Route::delete('/{id}', [AddressController::class, 'destroy']);
    });
    
    Route::prefix('ranches')->group(function () {
        Route::post('/', [RanchController::class, 'store']);
        Route::put('/{ranch}', [RanchController::class, 'update']);
        Route::delete('/{ranch}', [RanchController::class, 'destroy']);
        Route::post('/{ranch}/documents', [RanchController::class, 'uploadDocument']);
        Route::delete('/{ranch}/documents/{document}', [RanchController::class, 'deleteDocument']);
    });
    
    // Endpoints para "Mi Cuenta" (/me/*)
    Route::prefix('me')->group(function () {
        // Mis productos
        Route::get('/products', [\App\Http\Controllers\Marketplace\ProductController::class, 'myProducts']);
        
        // Mis haciendas/ranches
        Route::get('/ranches', [RanchController::class, 'myRanches']);
        
        // Mis métricas
        Route::get('/metrics', [ProfileController::class, 'myMetrics']);
    });

    // Orders (pedidos)
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::put('/orders/{order}', [OrderController::class, 'update']);
    Route::put('/orders/{order}/accept', [OrderController::class, 'accept']);
    Route::put('/orders/{order}/reject', [OrderController::class, 'reject']);
    Route::put('/orders/{order}/deliver', [OrderController::class, 'markAsDelivered']);
    Route::put('/orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::get('/orders/{order}/receipt', [OrderController::class, 'receipt']);
    Route::post('/orders/{order}/review', [OrderController::class, 'submitReview']);
    
    // Reportes
    Route::post('/reports', [\App\Http\Controllers\ReportController::class, 'store']);
    
});



// Rutas públicas para ubicaciones (países, estados, ciudades, parroquias)
Route::get('/countries', function() {
    return response()->json(\App\Models\Country::all());
});

Route::get('/states', function(Request $request) {
    $query = \App\Models\State::query();
    if ($request->has('country_id')) {
        $query->where('countries_id', $request->country_id);
    }
    return response()->json($query->get());
});

Route::get('/cities', function(Request $request) {
    $query = \App\Models\City::query();
    if ($request->has('state_id')) {
        $query->where('state_id', $request->state_id);
    }
    return response()->json($query->get());
});

Route::get('/parishes', function(Request $request) {
    $query = \App\Models\Parroquia::query();
    if ($request->has('city_id')) {
        $query->where('city_id', $request->city_id);
    }
    return response()->json($query->get());
});

// Ruta pública para tasa de cambio BCV
Route::get('/exchange-rate', [\App\Http\Controllers\ExchangeRateController::class, 'getBcvRate']);

// Ruta pública para pruebas
Route::get('/ping', fn() => response()->json(['message' => 'API funcionando']));

// Ruta de prueba para productos sin autenticación
// Route de prueba removida: columna 'disponible' no existe en products

// Ruta de prueba para verificar autenticación y rol
Route::get('/test/auth', function() {
    if (!Auth::check()) {
        return response()->json(['error' => 'No autenticado'], 401);
    }
    
    $user = Auth::user();
    return response()->json([
        'authenticated' => true,
        'user_id' => $user->id,
        'user_role' => $user->role,
        'user_email' => $user->email,
        'token_valid' => true
    ]);
// Removed stray closing middleware
})->middleware('auth:sanctum');