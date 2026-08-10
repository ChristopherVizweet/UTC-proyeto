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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teaching_assignment_id')
                ->constrained('teaching_assignments')
                ->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->text('instrucciones')->nullable();
            $table->string('tipo')->default('tarea')->index();
            $table->dateTime('fecha_publicacion')->nullable()->index();
            $table->dateTime('fecha_limite')->nullable()->index();
            $table->decimal('puntaje_maximo', 8, 2)->default(10);
            $table->boolean('permite_entrega_tardia')->default(false);
            $table->string('archivo_path')->nullable();
            $table->string('estado')->default('borrador')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
