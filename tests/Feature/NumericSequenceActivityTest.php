<?php

use App\Models\Activity;
use App\Models\ActivityAttempt;
use App\Models\Enrollment;
use App\Models\Submission;
use App\Models\TeachingAssignment;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['administrador', 'docente', 'estudiante'] as $role) {
        Role::findOrCreate($role, 'web');
    }

    $this->teacher = User::factory()->create();
    $this->teacher->assignRole('docente');
    $this->student = User::factory()->create();
    $this->student->assignRole('estudiante');
    $this->assignment = TeachingAssignment::factory()->for($this->teacher, 'teacher')->create();
    Enrollment::factory()->for($this->student, 'student')->for($this->assignment->schoolGroup)->create();
});

function numericSequenceData(TeachingAssignment $assignment, array $overrides = []): array
{
    return [
        'teaching_assignment_id' => $assignment->id,
        'titulo' => 'Completa la secuencia',
        'tipo' => 'secuencia',
        'fecha_publicacion' => now()->subMinute()->format('Y-m-d H:i:s'),
        'fecha_limite' => now()->addWeek()->format('Y-m-d H:i:s'),
        'puntaje_maximo' => 10,
        'permite_entrega_tardia' => false,
        'estado' => 'publicada',
        'sequence_values' => '1,2,3,4,5,6',
        'sequence_hidden_count' => 3,
        'max_attempts' => 2,
        'show_result_immediately' => true,
        ...$overrides,
    ];
}

it('permite crear una actividad de secuencia numérica', function () {
    $this->actingAs($this->teacher)
        ->post(route('activities.store'), numericSequenceData($this->assignment))
        ->assertRedirect();

    $activity = Activity::with('content')->firstOrFail();

    expect($activity->tipo)->toBe('secuencia')
        ->and($activity->content->configuracion['values'])->toBe(['1', '2', '3', '4', '5', '6'])
        ->and($activity->content->configuracion['hidden_count'])->toBe(3);
});

it('mantiene visible el primer valor y oculta posiciones aleatorias posteriores', function () {
    $this->actingAs($this->teacher)->post(route('activities.store'), numericSequenceData($this->assignment));
    $activity = Activity::firstOrFail();

    $this->actingAs($this->student)
        ->post(route('activities.numeric-sequence.attempts.start', $activity))
        ->assertRedirect();

    $attempt = ActivityAttempt::firstOrFail();

    expect($attempt->answers['hidden_indices'])->toHaveCount(3)
        ->and($attempt->answers['hidden_indices'])->not->toContain(0)
        ->and($attempt->answers['display_values'][0])->toBe('1');
});

it('califica automáticamente los valores ocultos', function () {
    $this->actingAs($this->teacher)->post(route('activities.store'), numericSequenceData($this->assignment));
    $activity = Activity::firstOrFail();
    $this->actingAs($this->student)->post(route('activities.numeric-sequence.attempts.start', $activity));
    $attempt = ActivityAttempt::firstOrFail();
    $answers = collect($attempt->answers['hidden_indices'])
        ->mapWithKeys(fn (int $index): array => [$index => $attempt->answers['solution'][$index]])
        ->all();

    $this->actingAs($this->student)
        ->post(route('activities.numeric-sequence.attempts.submit', $attempt), ['answers' => $answers])
        ->assertRedirect();

    expect($attempt->refresh()->score)->toBe('10.00')
        ->and($attempt->answers['correct_count'])->toBe(3)
        ->and(Submission::firstOrFail()->calificacion)->toBe('10.00');
});

it('rechaza secuencias con valores no numéricos', function () {
    $this->actingAs($this->teacher)
        ->post(route('activities.store'), numericSequenceData($this->assignment, [
            'sequence_values' => '1,2,tres,4',
        ]))
        ->assertSessionHasErrors('sequence_values');
});
