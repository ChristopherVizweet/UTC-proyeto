<?php

use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['administrador', 'docente', 'estudiante'] as $role) {
        Role::findOrCreate($role, 'web');
    }

    $this->administrator = User::factory()->create();
    $this->administrator->assignRole('administrador');
    $this->teacher = User::factory()->create();
    $this->teacher->assignRole('docente');
    $this->assignment = TeachingAssignment::factory()->for($this->teacher, 'teacher')->create();
});

function validActivityData(TeachingAssignment $assignment, array $overrides = []): array
{
    return [
        'teaching_assignment_id' => $assignment->id,
        'titulo' => 'Lectura de comprensión',
        'descripcion' => 'Lee el documento.',
        'instrucciones' => 'Contesta las preguntas.',
        'tipo' => 'tarea',
        'fecha_publicacion' => now()->format('Y-m-d H:i:s'),
        'fecha_limite' => now()->addWeek()->format('Y-m-d H:i:s'),
        'puntaje_maximo' => 10,
        'permite_entrega_tardia' => false,
        'estado' => 'publicada',
        ...$overrides,
    ];
}

it('permite al administrador crear una actividad', function () {
    $this->actingAs($this->administrator)
        ->post(route('activities.store'), validActivityData($this->assignment))
        ->assertRedirect();

    expect(Activity::first())
        ->titulo->toBe('Lectura de comprensión')
        ->created_by->toBe($this->administrator->id);
});

it('permite al docente crear una actividad en su asignación', function () {
    $this->actingAs($this->teacher)
        ->post(route('activities.store'), validActivityData($this->assignment))
        ->assertRedirect();

    expect(Activity::count())->toBe(1);
});

it('impide al docente crear una actividad en una asignación ajena', function () {
    $otherTeacher = User::factory()->create();
    $otherTeacher->assignRole('docente');
    $otherAssignment = TeachingAssignment::factory()->for($otherTeacher, 'teacher')->create();

    $this->actingAs($this->teacher)
        ->post(route('activities.store'), validActivityData($otherAssignment))
        ->assertSessionHasErrors('teaching_assignment_id');

    expect(Activity::count())->toBe(0);
});

it('impide al estudiante crear actividades', function () {
    $student = User::factory()->create();
    $student->assignRole('estudiante');

    $this->actingAs($student)
        ->post(route('activities.store'), validActivityData($this->assignment))
        ->assertForbidden();
});

it('permite al estudiante inscrito ver una actividad publicada', function () {
    $student = User::factory()->create();
    $student->assignRole('estudiante');
    Enrollment::factory()->for($student, 'student')->for($this->assignment->schoolGroup)->create();
    $activity = Activity::factory()->for($this->assignment)->for($this->teacher, 'creator')->create();

    $this->actingAs($student)->get(route('activities.show', $activity))->assertSuccessful();
});

it('impide que un estudiante de otro grupo vea la actividad', function () {
    $student = User::factory()->create();
    $student->assignRole('estudiante');
    Enrollment::factory()->for($student, 'student')->create();
    $activity = Activity::factory()->for($this->assignment)->for($this->teacher, 'creator')->create();

    $this->actingAs($student)->get(route('activities.show', $activity))->assertForbidden();
});

it('no muestra borradores al estudiante', function () {
    $student = User::factory()->create();
    $student->assignRole('estudiante');
    Enrollment::factory()->for($student, 'student')->for($this->assignment->schoolGroup)->create();
    $activity = Activity::factory()->draft()->for($this->assignment)->for($this->teacher, 'creator')->create();

    $this->actingAs($student)
        ->get(route('activities.index'))
        ->assertDontSee($activity->titulo);
});

it('guarda y reemplaza el archivo de una actividad eliminando el anterior', function () {
    Storage::fake('public');

    $this->actingAs($this->teacher)
        ->post(route('activities.store'), validActivityData($this->assignment, [
            'archivo' => UploadedFile::fake()->create('guia.pdf', 100, 'application/pdf'),
        ]));

    $activity = Activity::firstOrFail();
    Storage::disk('public')->assertExists($activity->archivo_path);
    $oldPath = $activity->archivo_path;

    $this->actingAs($this->teacher)
        ->put(route('activities.update', $activity), validActivityData($this->assignment, [
            'archivo' => UploadedFile::fake()->create('nueva.pdf', 100, 'application/pdf'),
        ]))
        ->assertRedirect();

    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($activity->refresh()->archivo_path);
});

it('impide descargar un archivo de actividad sin autorización', function () {
    Storage::fake('public');
    $path = UploadedFile::fake()->create('guia.pdf')->store('activities', 'public');
    $activity = Activity::factory()->for($this->assignment)->for($this->teacher, 'creator')->create(['archivo_path' => $path]);
    $student = User::factory()->create();
    $student->assignRole('estudiante');

    $this->actingAs($student)->get(route('activities.download', $activity))->assertForbidden();
});
