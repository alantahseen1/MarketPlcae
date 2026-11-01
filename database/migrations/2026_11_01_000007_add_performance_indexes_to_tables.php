<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product', function (Blueprint $table) {
            $table->index('status', 'idx_product_status');
            $table->index(['status', 'category_id'], 'idx_product_status_category');
            $table->index(['customer_id', 'status'], 'idx_product_customer_status');
            $table->index(['status', 'created_at'], 'idx_product_status_created_at');
        });

        Schema::table('report', function (Blueprint $table) {
            $table->index('status', 'idx_report_status');
            $table->index(['reported_product_id', 'status'], 'idx_report_product_status');
            $table->index(['reported_customer_id', 'status'], 'idx_report_customer_status');
        });

        Schema::table('category', function (Blueprint $table) {
            $table->index('is_active', 'idx_category_is_active');
        });

        Schema::table('customer', function (Blueprint $table) {
            $table->index('is_banned', 'idx_customer_is_banned');
            $table->index(['type', 'is_banned'], 'idx_customer_type_banned');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product', function (Blueprint $table) {
            $table->dropIndex('idx_product_status');
            $table->dropIndex('idx_product_status_category');
            $table->dropIndex('idx_product_customer_status');
            $table->dropIndex('idx_product_status_created_at');
        });

        Schema::table('report', function (Blueprint $table) {
            $table->dropIndex('idx_report_status');
            $table->dropIndex('idx_report_product_status');
            $table->dropIndex('idx_report_customer_status');
        });

        Schema::table('category', function (Blueprint $table) {
            $table->dropIndex('idx_category_is_active');
        });

        Schema::table('customer', function (Blueprint $table) {
            $table->dropIndex('idx_customer_is_banned');
            $table->dropIndex('idx_customer_type_banned');
        });
    }
};

