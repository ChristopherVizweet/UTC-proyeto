<?php

use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\Submission;
use App\Models\TeachingAssignment;
use App\Models\User;
use Spatie\Permission\Models\Role;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

it('muestra únicamente las actividades pendientes del estudiante', function () {
    Role::findOrCreate('estudiante', 'web');

    $student = User::factory()->create();
    $student->assignRole('estudiante');
    $assignment = TeachingAssignment::factory()->create();
    Enrollment::factory()->for($student, 'student')->for($assignment->schoolGroup)->create();

    $pendingActivity = Activity::factory()->for($assignment)->create(['titulo' => 'Actividad pendiente']);
    $completedActivity = Activity::factory()->for($assignment)->create(['titulo' => 'Actividad entregada']);
    Submission::factory()->for($completedActivity)->for($student, 'student')->create();

    $this->actingAs($student)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee($pendingActivity->titulo)
        ->assertDontSee($completedActivity->titulo);
});

it('muestra un mensaje cuando el estudiante no tiene actividades pendientes', function () {
    Role::findOrCreate('estudiante', 'web');

    $student = User::factory()->create();
    $student->assignRole('estudiante');

    $this->actingAs($student)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('¡Estamos al día con las actividades!');
});

it('muestra las materias registradas del estudiante', function () {
    Role::findOrCreate('estudiante', 'web');

    $student = User::factory()->create();
    $student->assignRole('estudiante');
    $registeredSubject = Subject::factory()->create(['nombre_materia' => 'Matemáticas']);
    $unregisteredSubject = Subject::factory()->create(['nombre_materia' => 'Historia']);
    $assignment = TeachingAssignment::factory()->for($registeredSubject, 'subject')->create();
    Enrollment::factory()->for($student, 'student')->for($assignment->schoolGroup)->create();

    $this->actingAs($student)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee($registeredSubject->nombre_materia)
        ->assertDontSee($unregisteredSubject->nombre_materia);
});
