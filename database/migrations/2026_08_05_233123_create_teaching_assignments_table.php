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
    Schema::create('teaching_assignments', function (Blueprint $table) {
        $table->id();

        $table->foreignId('teacher_id')
            ->constrained('users')
            ->cascadeOnUpdate()
            ->cascadeOnDelete();

        $table->foreignId('school_group_id')
            ->constrained('school_groups')
            ->cascadeOnUpdate()
            ->restrictOnDelete();

        $table->foreignId('subject_id')
            ->constrained('subjects')
            ->cascadeOnUpdate()
            ->restrictOnDelete();

        $table->boolean('is_active')->default(true);
        $table->timestamps();

        $table->unique(
            ['school_group_id', 'subject_id'],
            'teaching_group_subject_unique'
        );
    });
}

public function down(): void
{
    Schema::dropIfExists('teaching_assignments');
}
};
