<?php

use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\Submission;
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
    $this->student = User::factory()->create();
    $this->student->assignRole('estudiante');
    $this->assignment = TeachingAssignment::factory()->for($this->teacher, 'teacher')->create();
    $this->activity = Activity::factory()->for($this->assignment)->for($this->teacher, 'creator')->create();
    Enrollment::factory()->for($this->student, 'student')->for($this->assignment->schoolGroup)->create();
});

function submissionData(Activity $activity, string $action = 'submit', array $overrides = []): array
{
    return [
        'activity_id' => $activity->id,
        'respuesta' => 'Esta es mi respuesta.',
        'action' => $action,
        ...$overrides,
    ];
}

it('permite al estudiante inscrito guardar un borrador', function () {
    $this->actingAs($this->student)
        ->post(route('submissions.store'), submissionData($this->activity, 'draft'))
        ->assertRedirect();

    expect(Submission::first())
        ->student_id->toBe($this->student->id)
        ->estado->toBe('borrador')
        ->fecha_entrega->toBeNull();
});

it('permite al estudiante enviar una actividad', function () {
    $this->actingAs($this->student)
        ->post(route('submissions.store'), submissionData($this->activity))
        ->assertRedirect();

    expect(Submission::first())
        ->estado->toBe('entregada')
        ->fecha_entrega->not->toBeNull();
});

it('impide entregar al estudiante no inscrito', function () {
    $outsider = User::factory()->create();
    $outsider->assignRole('estudiante');

    $this->actingAs($outsider)
        ->post(route('submissions.store'), submissionData($this->activity))
        ->assertForbidden();
});

it('impide crear una entrega duplicada', function () {
    Submission::factory()->for($this->activity)->for($this->student, 'student')->create();

    $this->actingAs($this->student)
        ->post(route('submissions.store'), submissionData($this->activity))
        ->assertSessionHasErrors('activity_id');
});

it('impide al estudiante modificar una entrega ajena', function () {
    $otherStudent = User::factory()->create();
    $otherStudent->assignRole('estudiante');
    $submission = Submission::factory()->for($this->activity)->for($otherStudent, 'student')->create();

    $this->actingAs($this->student)
        ->put(route('submissions.update', $submission), ['respuesta' => 'Alterada', 'action' => 'draft'])
        ->assertForbidden();
});

it('rechaza una entrega vencida cuando no permite tardías', function () {
    $this->activity->update(['fecha_limite' => now()->subMinute(), 'permite_entrega_tardia' => false]);

    $this->actingAs($this->student)
        ->post(route('submissions.store'), submissionData($this->activity))
        ->assertSessionHasErrors('action');
});

it('marca una entrega permitida fuera de plazo como tardía', function () {
    $this->activity->update(['fecha_limite' => now()->subMinute(), 'permite_entrega_tardia' => true]);

    $this->actingAs($this->student)
        ->post(route('submissions.store'), submissionData($this->activity))
        ->assertRedirect();

    expect(Submission::first()->estado)->toBe('entregada_tarde');
});

it('ignora intentos del estudiante de alterar su calificación', function () {
    $this->actingAs($this->student)
        ->post(route('submissions.store'), submissionData($this->activity, 'submit', [
            'calificacion' => 10,
            'graded_by' => $this->student->id,
            'estado' => 'revisada',
        ]));

    expect(Submission::first())
        ->calificacion->toBeNull()
        ->graded_by->toBeNull()
        ->estado->toBe('entregada');
});

it('permite calificar al docente propietario', function () {
    $submission = Submission::factory()->for($this->activity)->for($this->student, 'student')->create();

    $this->actingAs($this->teacher)
        ->put(route('submissions.update-grade', $submission), ['calificacion' => 9, 'retroalimentacion' => 'Muy bien.'])
        ->assertRedirect();

    expect($submission->refresh())
        ->estado->toBe('revisada')
        ->graded_by->toBe($this->teacher->id)
        ->calificacion->toBe('9.00');
});

it('impide calificar a un docente ajeno', function () {
    $otherTeacher = User::factory()->create();
    $otherTeacher->assignRole('docente');
    $submission = Submission::factory()->for($this->activity)->for($this->student, 'student')->create();

    $this->actingAs($otherTeacher)
        ->put(route('submissions.update-grade', $submission), ['calificacion' => 9])
        ->assertForbidden();
});

it('impide que la calificación supere el puntaje máximo', function () {
    $submission = Submission::factory()->for($this->activity)->for($this->student, 'student')->create();

    $this->actingAs($this->teacher)
        ->put(route('submissions.update-grade', $submission), ['calificacion' => 11])
        ->assertSessionHasErrors('calificacion');
});

it('permite al administrador calificar cualquier entrega', function () {
    $submission = Submission::factory()->for($this->activity)->for($this->student, 'student')->create();

    $this->actingAs($this->administrator)
        ->put(route('submissions.update-grade', $submission), ['calificacion' => 8])
        ->assertRedirect();

    expect($submission->refresh()->graded_by)->toBe($this->administrator->id);
});

it('requiere autorización para descargar una entrega', function () {
    Storage::fake('public');
    $path = UploadedFile::fake()->create('respuesta.pdf')->store('submissions', 'public');
    $submission = Submission::factory()->for($this->activity)->for($this->student, 'student')->create(['archivo_path' => $path]);
    $outsider = User::factory()->create();
    $outsider->assignRole('estudiante');

    $this->actingAs($outsider)->get(route('submissions.download', $submission))->assertForbidden();
    $this->actingAs($this->student)->get(route('submissions.download', $submission))->assertDownload();
});
