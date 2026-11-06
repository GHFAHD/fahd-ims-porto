<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class StockMutation extends Model
{
    use HasFactory;

    public const TYPE_IN = 'in';
    public const TYPE_OUT = 'out';
    public const TYPE_ADJUSTMENT = 'adjustment';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'reference_number',
        'product_id',
        'user_id',
        'type',
        'quantity',
        'previous_stock',
        'current_stock',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'previous_stock' => 'integer',
            'current_stock' => 'integer',
        ];
    }

    /**
     * Get the product associated with this stock mutation.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who recorded this stock mutation.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a unique transaction reference number (e.g. IN-YYYYMMDD-XXXX).
     */
    public static function generateReferenceNumber(string $type): string
    {
        $prefix = match ($type) {
            self::TYPE_IN => 'IN',
            self::TYPE_OUT => 'OUT',
            self::TYPE_ADJUSTMENT => 'ADJ',
            default => 'MUT',
        };

        $datePart = now()->format('Ymd');

        do {
            $randomSuffix = Str::upper(Str::random(4));
            $reference = "{$prefix}-{$datePart}-{$randomSuffix}";
        } while (self::query()->where('reference_number', $reference)->exists());

        return $reference;
    }

    /**
     * Human-readable label for the mutation type.
     */
    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_IN => 'Stock In',
            self::TYPE_OUT => 'Stock Out',
            self::TYPE_ADJUSTMENT => 'Adjustment',
            default => ucfirst($this->type),
        };
    }

    /**
     * Net signed difference between current_stock and previous_stock.
     */
    public function signedDelta(): int
    {
        return $this->current_stock - $this->previous_stock;
    }
}

