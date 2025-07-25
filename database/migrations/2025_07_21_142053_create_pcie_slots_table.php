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

        Schema::create('pcie_slots', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('motherboard_id')->constrained()->onDelete('cascade');
            $table->decimal('version',2,1);
            $table->string('lane_type');
            $table->string('lane_type_notes')->nullable();
            $table->integer('quantity')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pcie_slots');
    }
};
