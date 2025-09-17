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
        Schema::create('pc_case_radiator_supports', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('pc_case_id')->constrained()->onDelete('cascade');
            $table->string('location');
            $table->integer('size_mm');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pc_case_radiator_supports');
    }
};
