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
            $table->integer('apartment_space');
            $table->integer('rent_price');
            $table->integer('floor');
            $table->smallInteger('bathrooms');
            $table->smallInteger('rooms');
            $table->string('Governorate');
            $table->string('city');
            $table->string('apartment_image')->nullable();
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
