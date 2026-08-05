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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('calle_usuario')->nullable();
            $table->string('colonia_usuario')->nullable();
            $table->string('exterior_usuario')->nullable();
            $table->string('interior_usuario')->nullable();
            $table->string('municipio_usuario')->nullable();
            $table->string('estado_usuario')->nullable();
            $table->string('cp_usuario')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
