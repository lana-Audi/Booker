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
        Schema::create('apartments', function (Blueprint $table) {
            $table->id();
        //    $table->foreignId('reservation_id')->constrained('users')->cascadeOnDelete();
            $table->integer('apartment_space');
            $table->integer('sale_price')->nullable();
            $table->integer('rent_price')->nullable();
            $table->integer('floor');
            $table->smallInteger('bathrooms');
            $table->smallInteger('rooms');
            $table->string('location');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartments');
    }
};
