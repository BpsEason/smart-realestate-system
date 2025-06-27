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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('address')->index();
            $table->decimal('area', 8, 2)->comment('面積 (坪)'); // Area uses DECIMAL type, precision 8, 2 decimal places
            $table->decimal('price', 12, 2)->comment('價格 (萬)'); // Price uses DECIMAL type, precision 12, 2 decimal places
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Revert the database migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
