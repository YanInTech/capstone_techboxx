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
        Schema::create('gpus', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('build_category_id')->constrained()->onDelete('cascade');
            $table->string('brand');
            $table->string('model');
            $table->string('vram_gb');
            $table->integer('power_draw_watts');
            $table->integer('recommended_psu_watt');
            $table->integer('length_mm');
            $table->string('pcie_interface');
            $table->string('connectors_required');
            $table->decimal('price',10,2);
            $table->integer('stock');
            $table->string('image')->nullable();
            $table->string('model_3d')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gpus');
    }
};
