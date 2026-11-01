<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerManagementService
{
    /**
     * Ban a customer.
     *
     * @throws ValidationException
     */
    public function ban(Customer $customer): void
    {
        if ($customer->is_banned) {
            throw ValidationException::withMessages([
                'error' => __('staff.customer_already_banned'),
            ]);
        }

        DB::transaction(function () use ($customer): void {
            $customer->update(['is_banned' => true]);
        });
    }

    /**
     * Unban a customer.
     *
     * @throws ValidationException
     */
    public function unban(Customer $customer): void
    {
        if (! $customer->is_banned) {
            throw ValidationException::withMessages([
                'error' => __('staff.customer_not_banned'),
            ]);
        }

        DB::transaction(function () use ($customer): void {
            $customer->update(['is_banned' => false]);
        });
    }

    /**
     * Verify a shop customer.
     *
     * @throws ValidationException
     */
    public function verify(Customer $customer): void
    {
        if ($customer->type !== 'shop') {
            throw ValidationException::withMessages([
                'error' => __('staff.only_shops_can_be_verified'),
            ]);
        }

        DB::transaction(function () use ($customer): void {
            $customer->update(['is_verified' => true]);
        });
    }

    /**
     * Unverify a shop customer.
     *
     * @throws ValidationException
     */
    public function unverify(Customer $customer): void
    {
        if ($customer->type !== 'shop') {
            throw ValidationException::withMessages([
                'error' => __('staff.only_shops_can_be_verified'),
            ]);
        }

        DB::transaction(function () use ($customer): void {
            $customer->update(['is_verified' => false]);
        });
    }
}

