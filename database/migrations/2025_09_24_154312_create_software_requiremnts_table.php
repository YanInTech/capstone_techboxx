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
        Schema::create('software_requiremnts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('software_id')->constrained()->onDelete('cascade');
            $table->string('cpu_min');
            $table->string('cpu_reco');
            $table->string('ram_min');
            $table->string('ram_reco');
            $table->string('gpu_min');
            $table->string('gpu_reco');
            $table->string('storage_min');
            $table->string('storgae_reco');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('software_requiremnts');
    }
};
