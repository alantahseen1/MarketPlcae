<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'report';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The name of the "updated at" column.
     *
     * @var string|null
     */
    public const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'reporter_id',
        'reported_product_id',
        'reported_customer_id',
        'reason',
        'details',
        'status',
        'handled_by',
        'handled_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'handled_at' => 'datetime',
        ];
    }

    /**
     * Get the customer who filed the report.
     *
     * @return BelongsTo<Customer, $this>
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'reporter_id');
    }

    /**
     * Get the product that was reported (if any).
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'reported_product_id');
    }

    /**
     * Get the customer who was reported (if any).
     *
     * @return BelongsTo<Customer, $this>
     */
    public function reportedCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'reported_customer_id');
    }

    /**
     * Get the staff user who handled this report.
     *
     * @return BelongsTo<User, $this>
     */
    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}

