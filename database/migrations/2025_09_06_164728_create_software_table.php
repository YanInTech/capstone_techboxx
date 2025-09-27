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
        Schema::create('software', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->string('icon')->nullable();
            $table->foreignId('build_category_id')->constrained()->onDelete('cascade');
            $table->string('os_min')->nullable();
            $table->string('cpu_min')->nullable();
            $table->string('cpu_reco')->nullable();
            $table->integer('ram_min')->nullable();
            $table->integer('ram_reco')->nullable();
            $table->string('gpu_min')->nullable();
            $table->string('gpu_reco')->nullable();
            $table->integer('storage_min')->nullable();
            $table->integer('storage_reco')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('software');
    }
};
