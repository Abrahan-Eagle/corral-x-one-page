<?php

namespace App\Http\Controllers\Orders;

use App\Events\OrderAccepted;
use App\Events\OrderCancelled;
use App\Events\OrderCompleted;
use App\Events\OrderCreated;
use App\Events\OrderDelivered;
use App\Events\OrderRejected;
use App\Events\OrderUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\CancelOrderRequest;
use App\Http\Requests\Orders\RejectOrderRequest;
use App\Http\Requests\Orders\StoreOrderRequest;
use App\Http\Requests\Orders\SubmitReviewRequest;
use App\Http\Requests\Orders\UpdateOrderRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Services\FirebaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    /**
     * Listar pedidos del usuario autenticado (como comprador o vendedor).
     */
    public function index(Request $request): JsonResponse
    {
        $profile = $this->profileOrAbort();

        $role = $request->query('role', 'buyer');
        $status = $request->query('status');
        $perPage = (int) $request->query('per_page', 15);

        $query = Order::query()->with($this->defaultRelations())->latest();

        if ($role === 'seller') {
            $query->where('seller_profile_id', $profile->id);
        } else {
            $query->where('buyer_profile_id', $profile->id);
        }

        if ($status) {
            $query->where('status', $status);
        }

        return response()->json($query->paginate($perPage));
    }

    /**
     * Crear un pedido nuevo desde el chat.
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $profile = $this->profileOrAbort();
        $data = $request->validated();

        $product = Product::with('ranch.profile')->findOrFail($data['product_id']);

        if ($product->ranch?->profile_id === $profile->id) {
            throw ValidationException::withMessages([
                'product_id' => 'No puedes crear un pedido sobre tu propio producto.',
            ]);
        }

        if ($product->quantity < $data['quantity']) {
            throw ValidationException::withMessages([
                'quantity' => 'La cantidad solicitada supera la disponibilidad del producto.',
            ]);
        }

        $unitPrice = $data['unit_price'] ?? $product->price;

        $order = Order::create([
            'product_id' => $product->id,
            'buyer_profile_id' => $profile->id,
            'seller_profile_id' => $product->ranch?->profile_id,
            'conversation_id' => $data['conversation_id'] ?? null,
            'ranch_id' => $product->ranch_id,
            'quantity' => $data['quantity'],
            'unit_price' => $unitPrice,
            'total_price' => $unitPrice * $data['quantity'],
            'currency' => $product->currency,
            'status' => 'pending',
            'delivery_method' => $data['delivery_method'],
            'pickup_location' => $data['pickup_location'],
            'pickup_address' => $data['pickup_address'] ?? null,
            'delivery_address' => $data['delivery_address'] ?? null,
            'pickup_notes' => $data['pickup_notes'] ?? null,
            'delivery_cost' => $data['delivery_cost'] ?? 0,
            'delivery_cost_currency' => $data['delivery_cost_currency'] ?? $product->currency,
            'delivery_provider' => $this->resolveDeliveryProvider($data),
            'delivery_tracking_number' => $data['delivery_tracking_number'] ?? null,
            'expected_pickup_date' => $data['expected_pickup_date'] ?? null,
            'buyer_notes' => $data['buyer_notes'] ?? null,
        ]);

        $order->load($this->defaultRelations());

        // 🔔 Broadcast evento y notificación push
        broadcast(new OrderCreated($order));
        $this->sendOrderPushNotification($order, 'created');

        return response()->json($order, Response::HTTP_CREATED);
    }

    /**
     * Mostrar un pedido específico (solo participantes).
     */
    public function show(Order $order): JsonResponse
    {
        $profile = $this->profileOrAbort();
        $this->ensureParticipant($order, $profile->id);

        return response()->json($order->load($this->defaultRelations()));
    }

    /**
     * Actualizar un pedido pendiente (solo vendedor).
     */
    public function update(UpdateOrderRequest $request, Order $order): JsonResponse
    {
        $data = $request->validated();
        
        // Validar que la cantidad no supere la disponibilidad del producto
        if (isset($data['quantity'])) {
            $product = Product::findOrFail($order->product_id);
            if ($product->quantity < $data['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => 'La cantidad solicitada supera la disponibilidad del producto.',
                ]);
            }
        }

        // Actualizar los campos proporcionados
        if (isset($data['quantity'])) {
            $order->quantity = $data['quantity'];
        }
        
        if (isset($data['unit_price'])) {
            $order->unit_price = $data['unit_price'];
        }

        // Recalcular total_price si cambió quantity o unit_price
        if (isset($data['quantity']) || isset($data['unit_price'])) {
            $order->total_price = $order->unit_price * $order->quantity;
        }

        if (isset($data['delivery_method'])) {
            $order->delivery_method = $data['delivery_method'];
        }

        if (isset($data['pickup_location'])) {
            $order->pickup_location = $data['pickup_location'];
        }

        if (isset($data['pickup_address'])) {
            $order->pickup_address = $data['pickup_address'];
        }

        if (isset($data['delivery_address'])) {
            $order->delivery_address = $data['delivery_address'];
        }

        if (isset($data['pickup_notes'])) {
            $order->pickup_notes = $data['pickup_notes'];
        }

        if (isset($data['delivery_cost'])) {
            $order->delivery_cost = $data['delivery_cost'];
        }

        if (isset($data['delivery_cost_currency'])) {
            $order->delivery_cost_currency = $data['delivery_cost_currency'];
        }

        if (isset($data['delivery_provider'])) {
            $order->delivery_provider = $data['delivery_provider'];
        }

        if (isset($data['delivery_tracking_number'])) {
            $order->delivery_tracking_number = $data['delivery_tracking_number'];
        }

        if (isset($data['expected_pickup_date'])) {
            $order->expected_pickup_date = $data['expected_pickup_date'];
        }

        if (isset($data['seller_notes'])) {
            $order->seller_notes = $data['seller_notes'];
        }

        $order->save();
        $order->refresh();
        $order->load($this->defaultRelations());

        // 🔔 Broadcast evento y notificación push
        broadcast(new OrderUpdated($order));
        $this->sendOrderPushNotification($order, 'updated');

        return response()->json($order);
    }

    /**
     * Aceptar un pedido (solo vendedor).
     */
    public function accept(Order $order): JsonResponse
    {
        $profile = $this->profileOrAbort();
        $this->ensureSeller($order, $profile->id);

        if (!$order->accept()) {
            return response()->json([
                'message' => 'El pedido no se puede aceptar en su estado actual.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $order->refresh();
        $order->load($this->defaultRelations());

        // 🔔 Broadcast evento y notificación push
        broadcast(new OrderAccepted($order));
        $this->sendOrderPushNotification($order, 'accepted');

        return response()->json($order);
    }

    /**
     * Rechazar un pedido (solo vendedor).
     */
    public function reject(RejectOrderRequest $request, Order $order): JsonResponse
    {
        $profile = $this->profileOrAbort();
        $this->ensureSeller($order, $profile->id);

        if (!$order->reject($request->validated('reason'))) {
            return response()->json([
                'message' => 'El pedido no se puede rechazar en su estado actual.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $order->refresh();
        $order->load($this->defaultRelations());

        // 🔔 Broadcast evento y notificación push
        broadcast(new OrderRejected($order));
        $this->sendOrderPushNotification($order, 'rejected');

        return response()->json($order);
    }

    /**
     * Confirmar que el comprador recogió/recibió el pedido.
     */
    public function markAsDelivered(Order $order): JsonResponse
    {
        $profile = $this->profileOrAbort();
        $this->ensureBuyer($order, $profile->id);

        if (!$order->markAsDelivered()) {
            return response()->json([
                'message' => 'El pedido no se puede marcar como entregado en su estado actual.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $order->refresh();
        $order->load($this->defaultRelations());

        // 🔔 Broadcast evento y notificación push
        broadcast(new OrderDelivered($order));
        $this->sendOrderPushNotification($order, 'delivered');

        return response()->json($order);
    }

    /**
     * Cancelar un pedido (comprador o vendedor).
     */
    public function cancel(CancelOrderRequest $request, Order $order): JsonResponse
    {
        $profile = $this->profileOrAbort();
        $this->ensureParticipant($order, $profile->id);

        if (!$order->cancel($request->validated('reason'))) {
            return response()->json([
                'message' => 'El pedido no se puede cancelar en su estado actual.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $order->refresh();
        $order->load($this->defaultRelations());

        // 🔔 Broadcast evento y notificación push
        broadcast(new OrderCancelled($order));
        $this->sendOrderPushNotification($order, 'cancelled');

        return response()->json($order);
    }

    /**
     * Obtener el comprobante de venta.
     */
    public function receipt(Order $order): JsonResponse
    {
        $profile = $this->profileOrAbort();
        $this->ensureParticipant($order, $profile->id);

        if ($order->isPending()) {
            return response()->json([
                'message' => 'El comprobante solo está disponible después de ser aceptado.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!$order->receipt_data) {
            $order->generateReceiptData();
            $order->save();
        }

        return response()->json([
            'receipt' => $order->receipt_data,
            'order' => $order->load($this->defaultRelations()),
        ]);
    }

    /**
     * Registrar las calificaciones post-compra.
     */
    public function submitReview(SubmitReviewRequest $request, Order $order): JsonResponse
    {
        $profile = $this->profileOrAbort();
        $this->ensureParticipant($order, $profile->id);

        if (!$order->isDelivered() && !$order->isCompleted()) {
            return response()->json([
                'message' => 'Solo se puede calificar pedidos entregados.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data = $request->validated();

        // Usar cast a int para manejar casos donde buyer_profile_id/seller_profile_id pueden ser strings desde la BD
        if ((int)$profile->id === (int)$order->buyer_profile_id) {
            $this->validateBuyerReviewData($data);
            $this->assertBuyerHasNotReviewed($order);

            Review::create([
                'order_id' => $order->id,
                'profile_id' => $profile->id,
                'product_id' => $order->product_id,
                'ranch_id' => $order->ranch_id,
                'rating' => $data['product_rating'],
                'comment' => $data['product_comment'] ?? null,
                'is_verified_purchase' => true,
                'is_approved' => true,
            ]);

            Review::create([
                'order_id' => $order->id,
                'profile_id' => $profile->id,
                'product_id' => null,
                'ranch_id' => $order->ranch_id,
                'rating' => $data['seller_rating'],
                'comment' => $data['seller_comment'] ?? null,
                'is_verified_purchase' => true,
                'is_approved' => true,
            ]);
        } elseif ((int)$profile->id === (int)$order->seller_profile_id) {
            $this->validateSellerReviewData($data);
            $this->assertSellerHasNotReviewed($order);

            Review::create([
                'order_id' => $order->id,
                'profile_id' => $profile->id,
                'product_id' => $order->product_id,
                'ranch_id' => $order->ranch_id,
                'rating' => $data['buyer_rating'],
                'comment' => $data['buyer_comment'] ?? null,
                'is_verified_purchase' => true,
                'is_approved' => true,
            ]);
        } else {
            abort(Response::HTTP_FORBIDDEN);
        }

        $order->refresh();

        if ($this->bothPartiesReviewed($order)) {
            $order->complete();
            $order->refresh();
            $order->load($this->defaultRelations());
            
            // 🔔 Broadcast evento y notificación push cuando se completa
            broadcast(new OrderCompleted($order));
            $this->sendOrderPushNotification($order, 'completed');
        } else {
            $order->load($this->defaultRelations());
        }

        return response()->json($order);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    protected function profileOrAbort()
    {
        $profile = Auth::user()?->profile;

        if (!$profile) {
            abort(Response::HTTP_FORBIDDEN, 'Perfil no disponible.');
        }

        return $profile;
    }

    protected function ensureParticipant(Order $order, int $profileId): void
    {
        // Usar cast a int para manejar casos donde buyer_profile_id/seller_profile_id pueden ser strings desde la BD
        $buyerId = (int)$order->buyer_profile_id;
        $sellerId = (int)$order->seller_profile_id;
        $profileIdInt = (int)$profileId;
        
        if ($buyerId !== $profileIdInt && $sellerId !== $profileIdInt) {
            \Log::warning('❌ Usuario no es participante del pedido', [
                'user_id' => Auth::id(),
                'profile_id' => $profileId,
                'profile_id_type' => gettype($profileId),
                'order_id' => $order->id,
                'order_buyer_id' => $order->buyer_profile_id,
                'order_buyer_id_type' => gettype($order->buyer_profile_id),
                'order_seller_id' => $order->seller_profile_id,
                'order_seller_id_type' => gettype($order->seller_profile_id),
                'comparison_buyer' => $buyerId === $profileIdInt ? 'MATCH' : 'NO_MATCH',
                'comparison_seller' => $sellerId === $profileIdInt ? 'MATCH' : 'NO_MATCH',
            ]);
            abort(Response::HTTP_FORBIDDEN, 'No participas en este pedido.');
        }
    }

    protected function ensureBuyer(Order $order, int $profileId): void
    {
        // Usar cast a int para manejar casos donde buyer_profile_id puede ser string desde la BD
        if ((int)$order->buyer_profile_id !== (int)$profileId) {
            \Log::warning('❌ Usuario no es comprador del pedido', [
                'user_id' => Auth::id(),
                'profile_id' => $profileId,
                'order_id' => $order->id,
                'order_buyer_id' => $order->buyer_profile_id,
            ]);
            abort(Response::HTTP_FORBIDDEN, 'Solo el comprador puede realizar esta acción.');
        }
    }

    protected function ensureSeller(Order $order, int $profileId): void
    {
        // Usar cast a int para manejar casos donde seller_profile_id puede ser string desde la BD
        if ((int)$order->seller_profile_id !== (int)$profileId) {
            \Log::warning('❌ Usuario no es vendedor del pedido', [
                'user_id' => Auth::id(),
                'profile_id' => $profileId,
                'order_id' => $order->id,
                'order_seller_id' => $order->seller_profile_id,
            ]);
            abort(Response::HTTP_FORBIDDEN, 'Solo el vendedor puede realizar esta acción.');
        }
    }

    protected function defaultRelations(): array
    {
        return [
            'product.images',
            'product.ranch.address.city',
            'buyer.user',
            'buyer.addresses.city.state',
            'buyer.ranches',
            'seller.user',
            'seller.addresses.city.state',
            'seller.ranches',
            'ranch.address.city',
            'conversation',
        ];
    }

    protected function resolveDeliveryProvider(array $data): ?string
    {
        if (($data['delivery_method'] ?? null) === 'corralx_delivery') {
            return $data['delivery_provider'] ?? 'CorralX Delivery';
        }

        if (($data['delivery_method'] ?? null) === 'external_delivery') {
            return $data['delivery_provider'] ?? null;
        }

        return $data['delivery_provider'] ?? null;
    }

    protected function validateBuyerReviewData(array $data): void
    {
        if (empty($data['product_rating']) || empty($data['seller_rating'])) {
            throw ValidationException::withMessages([
                'product_rating' => 'Debes calificar el producto y al vendedor.',
            ]);
        }
    }

    protected function validateSellerReviewData(array $data): void
    {
        if (empty($data['buyer_rating'])) {
            throw ValidationException::withMessages([
                'buyer_rating' => 'Debes calificar al comprador.',
            ]);
        }
    }

    protected function assertBuyerHasNotReviewed(Order $order): void
    {
        // Verificar si el comprador ya creó reviews para este pedido
        // El comprador crea 2 reviews: una para el producto y otra para el vendedor
        $productReviewExists = Review::where('order_id', $order->id)
            ->where('profile_id', $order->buyer_profile_id)
            ->where('product_id', $order->product_id)
            ->exists();

        $sellerReviewExists = Review::where('order_id', $order->id)
            ->where('profile_id', $order->buyer_profile_id)
            ->whereNull('product_id')
            ->exists();

        if ($productReviewExists || $sellerReviewExists) {
            throw ValidationException::withMessages([
                'order_id' => 'Ya registraste tus calificaciones para este pedido.',
            ]);
        }
    }

    protected function assertSellerHasNotReviewed(Order $order): void
    {
        // Verificar si el vendedor ya creó una review para este pedido
        // El vendedor crea 1 review: para el comprador (con product_id del pedido)
        $buyerReviewExists = Review::where('order_id', $order->id)
            ->where('profile_id', $order->seller_profile_id)
            ->where('product_id', $order->product_id)
            ->exists();

        if ($buyerReviewExists) {
            throw ValidationException::withMessages([
                'order_id' => 'Ya registraste tu calificación para este pedido.',
            ]);
        }
    }

    protected function bothPartiesReviewed(Order $order): bool
    {
        $buyerReviewed = Review::where('order_id', $order->id)
            ->where('profile_id', $order->buyer_profile_id)
            ->exists();

        $sellerReviewed = Review::where('order_id', $order->id)
            ->where('profile_id', $order->seller_profile_id)
            ->exists();

        return $buyerReviewed && $sellerReviewed;
    }

    /**
     * Enviar notificación push para eventos de pedidos
     */
    private function sendOrderPushNotification(Order $order, string $eventType): void
    {
        try {
            // Determinar receptor según el tipo de evento
            $receiverProfile = null;
            $senderProfile = Auth::user()->profile;
            
            switch ($eventType) {
                case 'created':
                    // Comprador creó pedido → notificar al vendedor
                    $receiverProfile = $order->seller;
                    break;
                case 'accepted':
                case 'rejected':
                case 'updated':
                    // Vendedor hizo acción → notificar al comprador
                    $receiverProfile = $order->buyer;
                    break;
                case 'delivered':
                case 'cancelled':
                    // Comprador hizo acción → notificar al vendedor
                    // O viceversa si el vendedor cancela
                    // Usar cast a int para manejar casos donde buyer_profile_id puede ser string desde la BD
                    if ((int)$order->buyer_profile_id === (int)$senderProfile->id) {
                        $receiverProfile = $order->seller;
                    } else {
                        $receiverProfile = $order->buyer;
                    }
                    break;
                case 'completed':
                    // Notificar a ambos
                    $this->sendOrderPushNotificationToProfile($order, $order->buyer, $eventType, $senderProfile);
                    $this->sendOrderPushNotificationToProfile($order, $order->seller, $eventType, $senderProfile);
                    return;
            }

            if ($receiverProfile) {
                $this->sendOrderPushNotificationToProfile($order, $receiverProfile, $eventType, $senderProfile);
            }
        } catch (\Exception $e) {
            \Log::error('❌ Error enviando notificación push de pedido', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
                'event_type' => $eventType,
            ]);
        }
    }

    /**
     * Enviar notificación push a un perfil específico
     */
    private function sendOrderPushNotificationToProfile(Order $order, $receiverProfile, string $eventType, $senderProfile): void
    {
        if (!$receiverProfile || !$receiverProfile->fcm_device_token) {
            return;
        }

        // Evitar notificar al mismo usuario que hizo la acción
        if ($receiverProfile->id === $senderProfile->id) {
            return;
        }

        $senderName = $senderProfile->firstName . ' ' . $senderProfile->lastName;
        $productName = $order->product->title ?? 'Producto';
        
        // Mensajes según tipo de evento
        $messages = [
            'created' => [
                'title' => 'Nuevo pedido recibido',
                'body' => "{$senderName} ha creado un pedido de {$productName}",
            ],
            'accepted' => [
                'title' => 'Pedido aceptado',
                'body' => "{$senderName} ha aceptado tu pedido de {$productName}",
            ],
            'rejected' => [
                'title' => 'Pedido rechazado',
                'body' => "{$senderName} ha rechazado tu pedido de {$productName}",
            ],
            'updated' => [
                'title' => 'Pedido actualizado',
                'body' => "{$senderName} ha actualizado el pedido de {$productName}",
            ],
            'delivered' => [
                'title' => 'Pedido entregado',
                'body' => "{$senderName} ha confirmado la entrega de {$productName}",
            ],
            'cancelled' => [
                'title' => 'Pedido cancelado',
                'body' => "{$senderName} ha cancelado el pedido de {$productName}",
            ],
            'completed' => [
                'title' => 'Pedido completado',
                'body' => "El pedido de {$productName} ha sido completado",
            ],
        ];

        $message = $messages[$eventType] ?? [
            'title' => 'Actualización de pedido',
            'body' => "Tu pedido de {$productName} ha sido actualizado",
        ];

        $firebaseService = new FirebaseService();
        $firebaseService->sendToDevice(
            $receiverProfile->fcm_device_token,
            $message['title'],
            $message['body'],
            [
                'order_id' => (string)$order->id,
                'type' => 'order_' . $eventType,
                'status' => $order->status,
                'product_id' => (string)$order->product_id,
                'timestamp' => now()->timestamp,
            ]
        );

        \Log::info('📬 Notificación push de pedido enviada', [
            'receiver_id' => $receiverProfile->id,
            'order_id' => $order->id,
            'event_type' => $eventType,
        ]);
    }
}
