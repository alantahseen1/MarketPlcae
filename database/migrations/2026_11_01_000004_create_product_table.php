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
        Schema::create('product', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('customer_id');
            $table->integer('category_id');
            $table->string('title', 150);
            $table->text('description');
            $table->unsignedInteger('price');
            $table->enum('condition', ['new', 'used']);
            $table->enum('status', ['pending', 'approved', 'rejected', 'sold', 'removed'])->default('pending');
            $table->integer('reviewed_by')->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->string('rejection_reason', 255)->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->dateTime('sold_at')->nullable();

            $table->foreign('customer_id')->references('id')->on('customer')->onDelete('restrict');
            $table->foreign('category_id')->references('id')->on('category')->onDelete('restrict');
            $table->foreign('reviewed_by')->references('id')->on('user')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product');
    }
};

