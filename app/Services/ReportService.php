<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReportService
{
    /**
     * Maximum number of reports a customer can submit per calendar day.
     */
    public const DAILY_LIMIT = 10;

    /**
     * Create a report for a product.
     *
     * @param  array{reason: string, details?: string|null}  $data
     *
     * @throws ValidationException
     */
    public function createProductReport(Customer $reporter, Product $product, array $data): Report
    {
        // 1. Cannot report own product
        if ($product->customer_id === $reporter->id) {
            throw ValidationException::withMessages([
                'error' => __('reports.cannot_report_own_product'),
            ]);
        }

        // 2. Product must be publicly visible
        if ($product->status !== 'approved' || ! $product->customer || $product->customer->is_banned || ! $product->category || ! $product->category->is_active) {
            throw ValidationException::withMessages([
                'error' => __('reports.product_not_visible'),
            ]);
        }

        // 3. Daily limit
        $this->ensureDailyLimitNotExceeded($reporter);

        // 4. Duplicate open report check
        $alreadyOpen = Report::query()
            ->where('reporter_id', $reporter->id)
            ->where('reported_product_id', $product->id)
            ->where('status', 'open')
            ->exists();

        if ($alreadyOpen) {
            throw ValidationException::withMessages([
                'error' => __('reports.already_reported_open'),
            ]);
        }

        return DB::transaction(function () use ($reporter, $product, $data): Report {
            return Report::create([
                'reporter_id' => $reporter->id,
                'reported_product_id' => $product->id,
                'reported_customer_id' => null,
                'reason' => $data['reason'],
                'details' => $data['details'] ?? null,
                'status' => 'open',
                'handled_by' => null,
                'handled_at' => null,
            ]);
        });
    }

    /**
     * Create a report for a customer / shop.
     *
     * @param  array{reason: string, details?: string|null}  $data
     *
     * @throws ValidationException
     */
    public function createCustomerReport(Customer $reporter, Customer $targetCustomer, array $data): Report
    {
        // 1. Cannot report self
        if ($targetCustomer->id === $reporter->id) {
            throw ValidationException::withMessages([
                'error' => __('reports.cannot_report_self'),
            ]);
        }

        // 2. Daily limit
        $this->ensureDailyLimitNotExceeded($reporter);

        // 3. Duplicate open report check
        $alreadyOpen = Report::query()
            ->where('reporter_id', $reporter->id)
            ->where('reported_customer_id', $targetCustomer->id)
            ->where('status', 'open')
            ->exists();

        if ($alreadyOpen) {
            throw ValidationException::withMessages([
                'error' => __('reports.already_reported_open'),
            ]);
        }

        return DB::transaction(function () use ($reporter, $targetCustomer, $data): Report {
            return Report::create([
                'reporter_id' => $reporter->id,
                'reported_product_id' => null,
                'reported_customer_id' => $targetCustomer->id,
                'reason' => $data['reason'],
                'details' => $data['details'] ?? null,
                'status' => 'open',
                'handled_by' => null,
                'handled_at' => null,
            ]);
        });
    }

    /**
     * Mark an open report as resolved.
     *
     * @throws ValidationException
     */
    public function resolve(Report $report, User $staff): Report
    {
        if ($report->status !== 'open') {
            throw ValidationException::withMessages([
                'error' => __('staff.report_already_closed'),
            ]);
        }

        return DB::transaction(function () use ($report, $staff): Report {
            $report->update([
                'status' => 'resolved',
                'handled_by' => $staff->id,
                'handled_at' => Carbon::now(),
            ]);

            return $report;
        });
    }

    /**
     * Mark an open report as dismissed.
     *
     * @throws ValidationException
     */
    public function dismiss(Report $report, User $staff): Report
    {
        if ($report->status !== 'open') {
            throw ValidationException::withMessages([
                'error' => __('staff.report_already_closed'),
            ]);
        }

        return DB::transaction(function () use ($report, $staff): Report {
            $report->update([
                'status' => 'dismissed',
                'handled_by' => $staff->id,
                'handled_at' => Carbon::now(),
            ]);

            return $report;
        });
    }

    /**
     * Remove a reported product (sets status = 'removed').
     * Does NOT close the report automatically.
     *
     * @throws ValidationException
     */
    public function removeProduct(Product $product): void
    {
        if ($product->status === 'removed') {
            throw ValidationException::withMessages([
                'error' => __('staff.product_already_removed'),
            ]);
        }

        DB::transaction(function () use ($product): void {
            $product->update([
                'status' => 'removed',
            ]);
        });
    }

    /**
     * Ensure the reporter has not reached the 10 reports/day limit.
     *
     * @throws ValidationException
     */
    protected function ensureDailyLimitNotExceeded(Customer $reporter): void
    {
        $todayCount = Report::query()
            ->where('reporter_id', $reporter->id)
            ->where('created_at', '>=', Carbon::today())
            ->count();

        if ($todayCount >= self::DAILY_LIMIT) {
            throw ValidationException::withMessages([
                'error' => __('reports.daily_limit_reached'),
            ]);
        }
    }
}
