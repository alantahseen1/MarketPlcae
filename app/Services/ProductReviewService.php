<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProductReviewService
{
    /**
     * Approve a pending product.
     */
    public function approve(Product $product, User $staff): bool
    {
        if ($product->status !== 'pending') {
            return false;
        }

        return DB::transaction(function () use ($product, $staff): bool {
            return $product->update([
                'status' => 'approved',
                'reviewed_by' => $staff->id,
                'reviewed_at' => now(),
                'rejection_reason' => null,
            ]);
        });
    }

    /**
     * Reject a pending product with a specific reason.
     */
    public function reject(Product $product, User $staff, string $reason): bool
    {
        if ($product->status !== 'pending') {
            return false;
        }

        return DB::transaction(function () use ($product, $staff, $reason): bool {
            return $product->update([
                'status' => 'rejected',
                'reviewed_by' => $staff->id,
                'reviewed_at' => now(),
                'rejection_reason' => $reason,
            ]);
        });
    }
}

