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
        Schema::create('user_builds', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('build_name');
            $table->foreignId('pc_case_id')->constrained()->onDelete('cascade');;
            $table->foreignId('motherboard_id')->constrained()->onDelete('cascade');
            $table->foreignId('cpu_id')->constrained()->onDelete('cascade');
            $table->foreignId('gpu_id')->constrained()->onDelete('cascade')->nullable();
            $table->foreignId('storage_id')->constrained()->onDelete('cascade');
            $table->foreignId('ram_id')->constrained()->onDelete('cascade');
            $table->foreignId('psu_id')->constrained()->onDelete('cascade');
            $table->foreignId('cooler_id')->constrained()->onDelete('cascade');
            $table->decimal('total_price',10,2);
            $table->string('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_builds');
    }
};
