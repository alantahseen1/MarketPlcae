<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'customer_id',
        'category_id',
        'title',
        'description',
        'price',
        'condition',
        'status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'sold_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'reviewed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'sold_at' => 'datetime',
        ];
    }

    /**
     * Scope a query to only include publicly visible products.
     * (status = 'approved' AND the seller is not banned).
     *
     * @param  Builder<Product>  $query
     */
    public function scopePubliclyVisible(Builder $query): void
    {
        $query->where($this->qualifyColumn('status'), 'approved')
            ->whereHas('customer', function (Builder $customerQuery): void {
                $customerQuery->where('is_banned', false);
            })
            ->whereHas('category', function (Builder $categoryQuery): void {
                $categoryQuery->where('is_active', true);
            });
    }

    /**
     * Filter query by search, category, condition, price range, and sorting.
     *
     * @param  Builder<Product>  $query
     * @param  array<string, mixed>  $filters
     */
    public function scopeFilter(Builder $query, array $filters): void
    {
        $query->when(! empty($filters['q']), function (Builder $q) use ($filters): void {
            $term = '%'.trim((string) $filters['q']).'%';
            $q->where(function (Builder $sub) use ($term): void {
                $sub->where('title', 'like', $term)
                    ->orWhere('description', 'like', $term);
            });
        });

        $query->when(! empty($filters['category']), function (Builder $q) use ($filters): void {
            $q->where('category_id', (int) $filters['category']);
        });

        $query->when(! empty($filters['condition']) && in_array($filters['condition'], ['new', 'used'], true), function (Builder $q) use ($filters): void {
            $q->where('condition', $filters['condition']);
        });

        $query->when(isset($filters['min_price']) && is_numeric($filters['min_price']) && $filters['min_price'] !== '', function (Builder $q) use ($filters): void {
            $q->where('price', '>=', (int) $filters['min_price']);
        });

        $query->when(isset($filters['max_price']) && is_numeric($filters['max_price']) && $filters['max_price'] !== '', function (Builder $q) use ($filters): void {
            $q->where('price', '<=', (int) $filters['max_price']);
        });

        $sort = $filters['sort'] ?? 'newest';
        if ($sort === 'price_asc') {
            $query->orderBy('price', 'asc')->orderBy('id', 'desc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('price', 'desc')->orderBy('id', 'desc');
        } else {
            $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');
        }
    }

    /**
     * Get the customer that owns the product.
     *
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Get the category that the product belongs to.
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Get the staff user that reviewed the product.
     *
     * @return BelongsTo<User, $this>
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the images for the product.
     *
     * @return HasMany<ProductImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }

    /**
     * Get the reports filed against this product.
     *
     * @return HasMany<Report, $this>
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'reported_product_id');
    }
}
