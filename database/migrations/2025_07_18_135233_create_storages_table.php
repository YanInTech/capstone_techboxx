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
        Schema::create('storages', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('build_category_id')->constrained()->onDelete('cascade');
            $table->string('brand');
            $table->string('model');
            $table->string('storage_type');
            $table->string('interface');
            $table->integer('capacity_gb');
            $table->string('form_factor');
            $table->integer('read_speed_mbps');
            $table->integer('write_speed_mbps');
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
        Schema::dropIfExists('storages');
    }
};
