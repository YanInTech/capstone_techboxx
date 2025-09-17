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
        Schema::create('coolers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('build_category_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->string('brand');
            $table->string('model');
            $table->string('cooler_type');
            $table->json('socket_compatibility');
            $table->integer('max_tdp');
            $table->integer('radiator_size_mm')->nullable();
            $table->integer('fan_count');
            $table->integer('height_mm');
            $table->decimal('price',10,2);
            $table->integer('stock');
            $table->string('image')->nullable();
            $table->string('model_3d')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coolers');
    }
};
