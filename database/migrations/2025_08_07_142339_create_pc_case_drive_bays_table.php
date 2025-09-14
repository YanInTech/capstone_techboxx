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
        Schema::create('pc_case_drive_bays', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('pc_case_id')->constrained()->onDelete('cascade');
            $table->integer('3_5_bays');
            $table->integer('2_5_bays');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pc_case_drive_bays');
    }
};
