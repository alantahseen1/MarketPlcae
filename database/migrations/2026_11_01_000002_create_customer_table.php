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
        Schema::create('customer', function (Blueprint $table) {
            $table->integer('id', true);
            $table->enum('type', ['regular', 'shop'])->default('regular');
            $table->string('full_name', 100);
            $table->string('shop_name', 100)->nullable();
            $table->string('logo', 255)->nullable();
            $table->string('phone', 20);
            $table->string('email', 150)->unique();
            $table->string('password_hash', 255);
            $table->string('address', 255);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_banned')->default(false);
            $table->dateTime('created_at')->useCurrent();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `customer` ADD CONSTRAINT `chk_shop_name` CHECK ((type = 'shop' AND shop_name IS NOT NULL) OR (type = 'regular' AND shop_name IS NULL))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer');
    }
};
