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
        Schema::create('ordered_builds', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('user_build_id')->constrained()->onDelete('cascade');
            $table->string('status')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->nullable();
            $table->string('payment_status');
            $table->string('payment_method');
            $table->string('pickup_status')->nullable();
            $table->timestamp('pickup_date')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordered_builds');
    }
};
