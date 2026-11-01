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
        Schema::create('visit', function (Blueprint $table) {
            $table->increments('id');
            $table->string('visitor_hash', 64);
            $table->date('visit_date');
            $table->unsignedInteger('page_views')->default(1);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();

            $table->unique(['visitor_hash', 'visit_date'], 'uniq_visit_visitor_date');
            $table->index('visit_date', 'idx_visit_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visit');
    }
};

