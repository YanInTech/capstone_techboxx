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
        Schema::create('pc_case_front_usb_ports', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('pc_case_id')->constrained()->onDelete('cascade');
            $table->integer('usb_3_0_type-A');
            $table->integer('usb_2_0');
            $table->integer('usb-c');
            $table->integer('audio_jacks');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pc_case_front_usb_ports');
    }
};
