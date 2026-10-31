<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('report', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('reporter_id');
            $table->integer('reported_product_id')->nullable();
            $table->integer('reported_customer_id')->nullable();
            $table->enum('reason', ['spam', 'scam', 'fake_item', 'other']);
            $table->text('details')->nullable();
            $table->enum('status', ['open', 'resolved', 'dismissed'])->default('open');
            $table->integer('handled_by')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('handled_at')->nullable();

            $table->foreign('reporter_id')->references('id')->on('customer')->onDelete('restrict');
            $table->foreign('reported_product_id')->references('id')->on('product')->onDelete('cascade');
            $table->foreign('reported_customer_id')->references('id')->on('customer')->onDelete('cascade');
            $table->foreign('handled_by')->references('id')->on('user')->onDelete('set null');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `report` ADD CONSTRAINT `chk_one_target` CHECK ((reported_product_id IS NOT NULL AND reported_customer_id IS NULL) OR (reported_product_id IS NULL AND reported_customer_id IS NOT NULL))');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report');
    }
};
