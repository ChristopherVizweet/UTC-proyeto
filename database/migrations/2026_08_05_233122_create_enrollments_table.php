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
    Schema::create('enrollments', function (Blueprint $table) {
        $table->id();

        $table->foreignId('student_id')
            ->constrained('users')
            ->cascadeOnUpdate()
            ->cascadeOnDelete();

        $table->foreignId('school_group_id')
            ->constrained('school_groups')
            ->cascadeOnUpdate()
            ->restrictOnDelete();

        $table->date('fecha_inscripcion');
        $table->string('estado_inscripcion')
            ->default('activo');

        $table->timestamps();

        $table->unique(
            ['student_id', 'school_group_id'],
            'enrollment_student_group_unique'
        );
    });
}

public function down(): void
{
    Schema::dropIfExists('enrollments');
}
};
