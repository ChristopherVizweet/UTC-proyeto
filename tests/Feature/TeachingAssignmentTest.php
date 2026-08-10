<?php

use App\Models\AcademicPeriod;
use App\Models\SchoolGrade;
use App\Models\SchoolGroup;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('administrador', 'web');
    Role::findOrCreate('docente', 'web');
    Role::findOrCreate('estudiante', 'web');

    $this->administrator = User::factory()->create();
    $this->administrator->assignRole('administrador');

    $this->teacher = User::factory()->create();
    $this->teacher->assignRole('docente');

    $period = AcademicPeriod::create([
        'nombre_periodo' => '2026-2027',
        'fecha_inicio' => '2026-08-01',
        'fecha_fin' => '2027-07-15',
        'estado_periodo' => true,
    ]);

    $grade = SchoolGrade::create([
        'nombre_grado' => 'Primero',
        'nivel_grado' => 1,
        'descripcion_grado' => 'Primer grado',
    ]);

    $this->schoolGroup = SchoolGroup::create([
        'school_grade_id' => $grade->id,
        'academic_period_id' => $period->id,
        'nombre_grupo' => 'A',
        'capacidad_grupo' => 30,
        'is_active' => true,
    ]);

    $this->subject = Subject::create([
        'nombre_materia' => 'Matemáticas',
        'code' => 'MAT-01',
        'is_active' => true,
    ]);
});

it('permite administrar asignaciones solamente al administrador', function () {
    $this->get(route('teaching-assignments.index'))
        ->assertRedirect(route('login'));

    $teacher = User::factory()->create();
    $teacher->assignRole('docente');

    $this->actingAs($teacher)
        ->get(route('teaching-assignments.index'))
        ->assertForbidden();

    $this->actingAs($this->administrator)
        ->get(route('teaching-assignments.index'))
        ->assertSuccessful();
});

it('crea una asignación para un docente', function () {
    $response = $this->actingAs($this->administrator)
        ->post(route('teaching-assignments.store'), [
            'teacher_id' => $this->teacher->id,
            'school_group_id' => $this->schoolGroup->id,
            'subject_id' => $this->subject->id,
            'is_active' => true,
        ]);

    $response
        ->assertRedirect(route('teaching-assignments.index'))
        ->assertSessionHas('success');

    $assignment = TeachingAssignment::first();

    expect($assignment)
        ->not->toBeNull()
        ->teacher_id->toBe($this->teacher->id)
        ->school_group_id->toBe($this->schoolGroup->id)
        ->subject_id->toBe($this->subject->id);
});

it('rechaza un usuario que no tiene rol docente', function () {
    $student = User::factory()->create();
    $student->assignRole('estudiante');

    $this->actingAs($this->administrator)
        ->from(route('teaching-assignments.create'))
        ->post(route('teaching-assignments.store'), [
            'teacher_id' => $student->id,
            'school_group_id' => $this->schoolGroup->id,
            'subject_id' => $this->subject->id,
            'is_active' => true,
        ])
        ->assertRedirect(route('teaching-assignments.create'))
        ->assertSessionHasErrors('teacher_id');

    expect(TeachingAssignment::count())->toBe(0);
});

it('rechaza una materia duplicada en el mismo grupo', function () {
    TeachingAssignment::create([
        'teacher_id' => $this->teacher->id,
        'school_group_id' => $this->schoolGroup->id,
        'subject_id' => $this->subject->id,
        'is_active' => true,
    ]);

    $otherTeacher = User::factory()->create();
    $otherTeacher->assignRole('docente');

    $this->actingAs($this->administrator)
        ->post(route('teaching-assignments.store'), [
            'teacher_id' => $otherTeacher->id,
            'school_group_id' => $this->schoolGroup->id,
            'subject_id' => $this->subject->id,
            'is_active' => true,
        ])
        ->assertSessionHasErrors('subject_id');

    expect(TeachingAssignment::count())->toBe(1);
});

it('actualiza una asignación ignorando su propia combinación', function () {
    $assignment = TeachingAssignment::create([
        'teacher_id' => $this->teacher->id,
        'school_group_id' => $this->schoolGroup->id,
        'subject_id' => $this->subject->id,
        'is_active' => true,
    ]);

    $otherTeacher = User::factory()->create();
    $otherTeacher->assignRole('docente');

    $this->actingAs($this->administrator)
        ->put(route('teaching-assignments.update', $assignment), [
            'teacher_id' => $otherTeacher->id,
            'school_group_id' => $this->schoolGroup->id,
            'subject_id' => $this->subject->id,
            'is_active' => false,
        ])
        ->assertRedirect(route('teaching-assignments.index'))
        ->assertSessionHasNoErrors();

    expect($assignment->refresh())
        ->teacher_id->toBe($otherTeacher->id)
        ->is_active->toBeFalse();
});

it('elimina una asignación docente', function () {
    $assignment = TeachingAssignment::create([
        'teacher_id' => $this->teacher->id,
        'school_group_id' => $this->schoolGroup->id,
        'subject_id' => $this->subject->id,
        'is_active' => true,
    ]);

    $this->actingAs($this->administrator)
        ->delete(route('teaching-assignments.destroy', $assignment))
        ->assertRedirect(route('teaching-assignments.index'))
        ->assertSessionHas('success');

    $this->assertModelMissing($assignment);
});
