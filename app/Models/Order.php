<?php

namespace App\Models;

use App\Models\Product;
use App\Models\Profile;
use App\Models\Conversation;
use App\Models\Ranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'buyer_profile_id',
        'seller_profile_id',
        'conversation_id',
        'ranch_id',
        'quantity',
        'unit_price',
        'total_price',
        'currency',
        'status',
        'delivery_method',
        'pickup_location',
        'pickup_address',
        'delivery_address',
        'pickup_notes',
        'delivery_cost',
        'delivery_cost_currency',
        'delivery_provider',
        'delivery_tracking_number',
        'expected_pickup_date',
        'actual_pickup_date',
        'buyer_notes',
        'seller_notes',
        'receipt_number',
        'receipt_data',
        'accepted_at',
        'rejected_at',
        'delivered_at',
        'completed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'delivery_cost' => 'decimal:2',
        'receipt_data' => 'array',
        'expected_pickup_date' => 'date',
        'actual_pickup_date' => 'date',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'delivered_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'buyer_profile_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'seller_profile_id');
    }

    public function ranch(): BelongsTo
    {
        return $this->belongsTo(Ranch::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Estados
    |--------------------------------------------------------------------------
    */

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /*
    |--------------------------------------------------------------------------
    | Transiciones de estado
    |--------------------------------------------------------------------------
    */

    public function accept(): bool
    {
        if (!$this->isPending()) {
            return false;
        }

        return DB::transaction(function () {
            $this->status = 'accepted';
            $this->accepted_at = now();

            if (!$this->receipt_number) {
                $this->receipt_number = sprintf(
                    'CORRALX-%08d-%s',
                    $this->id ?? $this->getKey(),
                    now()->format('Ymd')
                );
            }

            $this->generateReceiptData();
            $this->save();

            if ($product = $this->product()->lockForUpdate()->first()) {
                $product->decrement('quantity', $this->quantity);

                if ($product->quantity <= 0 && $product->status !== 'sold') {
                    $product->update(['status' => 'sold']);
                }
            }

            return true;
        });
    }

    public function reject(?string $reason = null): bool
    {
        if (!$this->isPending()) {
            return false;
        }

        $this->status = 'rejected';
        $this->rejected_at = now();

        if ($reason) {
            $this->seller_notes = trim(($this->seller_notes ?? '') . PHP_EOL . "Motivo: {$reason}");
        }

        return $this->save();
    }

    public function markAsDelivered(): bool
    {
        if (!$this->isAccepted()) {
            return false;
        }

        $this->status = 'delivered';
        $this->actual_pickup_date = now();
        $this->delivered_at = now();

        return $this->save();
    }

    public function complete(): bool
    {
        if (!$this->isDelivered()) {
            return false;
        }

        $this->status = 'completed';
        $this->completed_at = now();
        $saved = $this->save();

        $this->updateRatings();

        return $saved;
    }

    public function cancel(?string $reason = null): bool
    {
        if ($this->isCompleted() || $this->isCancelled()) {
            return false;
        }

        return DB::transaction(function () use ($reason) {
            $this->status = 'cancelled';
            $this->cancelled_at = now();

            if (in_array($this->status, ['accepted', 'delivered'])) {
                if ($product = $this->product()->lockForUpdate()->first()) {
                    $product->increment('quantity', $this->quantity);

                    if ($product->status === 'sold') {
                        $product->update(['status' => 'active']);
                    }
                }
            }

            if ($reason) {
                $this->buyer_notes = trim(($this->buyer_notes ?? '') . PHP_EOL . "Motivo cancelación: {$reason}");
            }

            return $this->save();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function generateReceiptData(): void
    {
        $this->loadMissing([
            'product.ranch.address.city',
            'buyer.user',
            'seller.user',
            'ranch.address.city',
        ]);

        $ranch = $this->ranch;
        $sellerProfile = $this->seller;
        $buyerProfile = $this->buyer;
        $product = $this->product;
        $address = $ranch?->address;

        $pickupAddress = $this->pickup_location === 'other'
            ? $this->pickup_address
            : ($address?->full_address ?? null);

        $deliveryInfo = $this->getDeliveryInfo();

        $this->receipt_data = [
            'receipt_number' => $this->receipt_number,
            'issue_date' => optional($this->accepted_at)->format('Y-m-d H:i:s'),
            'seller' => [
                'name' => $sellerProfile?->fullName ?? null,
                'ranch_name' => $ranch?->name,
                'legal_name' => $ranch?->legal_name,
                'tax_id' => $ranch?->tax_id,
                'address' => $pickupAddress,
                'phone' => $sellerProfile?->phones()->first()?->phone ?? null,
                'email' => $sellerProfile?->user?->email,
            ],
            'buyer' => [
                'name' => $buyerProfile?->fullName ?? null,
                'ci_number' => $buyerProfile?->ci_number,
                'address' => $this->delivery_address,
            ],
            'product' => [
                'title' => $product?->title,
                'type' => $product?->type,
                'breed' => $product?->breed,
                'quantity' => $this->quantity,
                'unit_price' => $this->unit_price,
                'total_price' => $this->total_price,
                'currency' => $this->currency,
            ],
            'delivery' => [
                'method' => $deliveryInfo['method_name'],
                'method_code' => $this->delivery_method,
                'pickup_location' => $this->pickup_location === 'ranch' ? 'En la finca' : 'Otro lugar',
                'pickup_address' => $pickupAddress,
                'delivery_address' => $this->delivery_address,
                'cost' => (float) $this->delivery_cost,
                'cost_currency' => $this->delivery_cost_currency,
                'provider' => $this->delivery_provider,
                'tracking_number' => $this->delivery_tracking_number,
                'expected_date' => optional($this->expected_pickup_date)->format('Y-m-d'),
                'notes' => $this->pickup_notes,
            ],
            'notes' => [
                'buyer' => $this->buyer_notes,
                'seller' => $this->seller_notes,
            ],
        ];
    }

    public function getDeliveryInfo(): array
    {
        return [
            'buyer_transport' => [
                'method_name' => 'Comprador lleva su transporte',
                'description' => 'El comprador se encarga de recoger el ganado con su propio transporte.',
            ],
            'seller_transport' => [
                'method_name' => 'Vendedor provee transporte',
                'description' => 'El vendedor coordina el traslado del ganado hasta la dirección del comprador.',
            ],
            'external_delivery' => [
                'method_name' => 'Delivery contratado externo',
                'description' => 'Un servicio de transporte de terceros realiza la entrega.',
            ],
            'corralx_delivery' => [
                'method_name' => 'Delivery interno de CorralX',
                'description' => 'CorralX gestiona la logística y seguimiento del envío.',
            ],
        ][$this->delivery_method] ?? [
            'method_name' => 'Método no especificado',
            'description' => null,
        ];
    }

    public function updateRatings(): void
    {
        $product = $this->product()->first();
        $ranch = $this->ranch()->first();

        if ($product) {
            $average = $product->reviews()->where('is_approved', true)->avg('rating');
            if ($average !== null && $product->isFillable('avg_rating')) {
                $product->update(['avg_rating' => $average]);
            }
        }

        if ($ranch) {
            $average = $ranch->reviews()->where('is_approved', true)->avg('rating');
            if ($average !== null && $ranch->isFillable('avg_rating')) {
                $ranch->update(['avg_rating' => $average]);
            }
        }
    }
}

