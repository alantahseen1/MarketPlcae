<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\Product;

class ProductPolicy
{
    /**
     * Determine whether the customer can view the product.
     */
    public function view(Customer $customer, Product $product): bool
    {
        return $customer->id === $product->customer_id;
    }

    /**
     * Determine whether the customer can update the product.
     */
    public function update(Customer $customer, Product $product): bool
    {
        return $customer->id === $product->customer_id
            && in_array($product->status, ['pending', 'approved', 'rejected'], true);
    }

    /**
     * Determine whether the customer can delete the product.
     */
    public function delete(Customer $customer, Product $product): bool
    {
        return $customer->id === $product->customer_id;
    }

    /**
     * Determine whether the customer can mark the product as sold.
     */
    public function markSold(Customer $customer, Product $product): bool
    {
        return $customer->id === $product->customer_id
            && $product->status === 'approved';
    }
}
