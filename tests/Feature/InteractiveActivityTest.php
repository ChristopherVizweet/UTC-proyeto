<?php

use App\Models\Activity;
use App\Models\ActivityAttempt;
use App\Models\ActivityContent;
use App\Models\Enrollment;
use App\Models\Submission;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Support\Str;
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

function interactivePairs(int $count = 2): array
{
    return collect(range(1, $count))
        ->map(fn (int $number) => [
            'left' => "Concepto {$number}",
            'right' => "Respuesta {$number}",
            'id' => 'id-enviado-por-cliente-'.$number,
        ])
        ->all();
}

function interactiveActivityData(TeachingAssignment $assignment, array $overrides = []): array
{
    return [
        'teaching_assignment_id' => $assignment->id,
        'titulo' => 'Capitales del mundo',
        'descripcion' => 'Relaciona cada país con su capital.',
        'instrucciones' => 'Selecciona una respuesta para cada concepto.',
        'tipo' => 'relacion_columnas',
        'fecha_publicacion' => now()->subMinute()->format('Y-m-d H:i:s'),
        'fecha_limite' => now()->addWeek()->format('Y-m-d H:i:s'),
        'puntaje_maximo' => 10,
        'permite_entrega_tardia' => false,
        'estado' => 'publicada',
        'pairs' => interactivePairs(),
        'shuffle_right_column' => true,
        'max_attempts' => 2,
        'show_result_immediately' => true,
        ...$overrides,
    ];
}

function createInteractiveActivity(TeachingAssignment $assignment, User $teacher, array $configuration = [], array $activityData = []): Activity
{
    $activity = Activity::factory()
        ->for($assignment)
        ->for($teacher, 'creator')
        ->create([
            'tipo' => 'relacion_columnas',
            ...$activityData,
        ]);

    ActivityContent::factory()->for($activity)->create([
        'configuracion' => [
            'pairs' => [
                ['id' => (string) Str::uuid(), 'left' => 'México', 'right' => 'Ciudad de México'],
                ['id' => (string) Str::uuid(), 'left' => 'Francia', 'right' => 'París'],
            ],
            'shuffle_right_column' => true,
            'max_attempts' => 2,
            'show_result_immediately' => true,
            ...$configuration,
        ],
    ]);

    return $activity;
}

function startInteractiveAttempt($test, Activity $activity, User $student): ActivityAttempt
{
    $test->actingAs($student)
        ->post(route('activities.attempts.start', $activity))
        ->assertRedirect();

    return ActivityAttempt::query()->latest('id')->firstOrFail();
}

it('permite al docente crear una relación de columnas con ids generados por el servidor', function () {
    $this->actingAs($this->teacher)
        ->post(route('activities.store'), interactiveActivityData($this->assignment))
        ->assertRedirect();

    $activity = Activity::with('content')->firstOrFail();

    expect($activity->tipo)->toBe('relacion_columnas')
        ->and($activity->content->configuracion['pairs'])->toHaveCount(2)
        ->and($activity->content->configuracion['pairs'][0]['id'])->not->toBe('id-enviado-por-cliente-1');
});

it('valida el mínimo y máximo de relaciones', function (int $count) {
    $this->actingAs($this->teacher)
        ->post(route('activities.store'), interactiveActivityData($this->assignment, [
            'pairs' => interactivePairs($count),
        ]))
        ->assertSessionHasErrors('pairs');
})->with([1, 21]);

it('permite iniciar al estudiante con inscripción activa', function () {
    $activity = createInteractiveActivity($this->assignment, $this->teacher);

    $this->actingAs($this->student)
        ->post(route('activities.attempts.start', $activity))
        ->assertRedirect();

    expect(ActivityAttempt::first())
        ->student_id->toBe($this->student->id)
        ->status->toBe('iniciado');
});

it('impide iniciar a un estudiante de otro grupo', function () {
    $outsider = User::factory()->create();
    $outsider->assignRole('estudiante');
    $activity = createInteractiveActivity($this->assignment, $this->teacher);

    $this->actingAs($outsider)
        ->post(route('activities.attempts.start', $activity))
        ->assertForbidden();
});

it('no expone el mapa de respuestas correctas en la vista', function () {
    $activity = createInteractiveActivity($this->assignment, $this->teacher);
    $attempt = startInteractiveAttempt($this, $activity, $this->student);
    $solutionJson = json_encode($attempt->answers['solution']);

    $this->actingAs($this->student)
        ->get(route('activities.attempts.result', $attempt))
        ->assertSuccessful()
        ->assertDontSee($solutionJson, false)
        ->assertDontSee($activity->content->configuracion['pairs'][0]['id']);
});

it('calcula la puntuación en el servidor e ignora score del cliente', function () {
    $activity = createInteractiveActivity($this->assignment, $this->teacher);
    $attempt = startInteractiveAttempt($this, $activity, $this->student);
    $solution = $attempt->answers['solution'];
    $rightIds = array_values($solution);
    $leftIds = array_keys($solution);

    $this->actingAs($this->student)
        ->post(route('activities.attempts.submit', $attempt), [
            'answers' => [
                $leftIds[0] => $rightIds[0],
                $leftIds[1] => $rightIds[0],
            ],
            'score' => 10,
        ])
        ->assertSessionHasErrors('answers.'.$leftIds[1]);

    $this->actingAs($this->student)
        ->post(route('activities.attempts.submit', $attempt), [
            'answers' => [
                $leftIds[0] => $rightIds[0],
                $leftIds[1] => $rightIds[1],
            ],
            'score' => 0,
        ])
        ->assertRedirect();

    expect($attempt->refresh()->score)->toBe('10.00');
});

it('impide enviar un intento ajeno', function () {
    $activity = createInteractiveActivity($this->assignment, $this->teacher);
    $attempt = startInteractiveAttempt($this, $activity, $this->student);
    $outsider = User::factory()->create();
    $outsider->assignRole('estudiante');

    $this->actingAs($outsider)
        ->post(route('activities.attempts.submit', $attempt), ['answers' => $attempt->answers['solution']])
        ->assertForbidden();
});

it('impide enviar dos veces el mismo intento', function () {
    $activity = createInteractiveActivity($this->assignment, $this->teacher);
    $attempt = startInteractiveAttempt($this, $activity, $this->student);

    $this->actingAs($this->student)
        ->post(route('activities.attempts.submit', $attempt), ['answers' => $attempt->answers['solution']])
        ->assertRedirect();

    $this->actingAs($this->student)
        ->post(route('activities.attempts.submit', $attempt), ['answers' => $attempt->answers['solution']])
        ->assertForbidden();
});

it('respeta la cantidad máxima de intentos', function () {
    $activity = createInteractiveActivity($this->assignment, $this->teacher, ['max_attempts' => 1]);
    $attempt = startInteractiveAttempt($this, $activity, $this->student);

    $this->actingAs($this->student)
        ->post(route('activities.attempts.submit', $attempt), ['answers' => $attempt->answers['solution']]);

    $this->actingAs($this->student)
        ->post(route('activities.attempts.start', $activity))
        ->assertSessionHasErrors('attempt');
});

it('actualiza la submission con el mejor intento', function () {
    $activity = createInteractiveActivity($this->assignment, $this->teacher);
    $first = startInteractiveAttempt($this, $activity, $this->student);
    $solution = $first->answers['solution'];
    $wrong = array_reverse(array_values($solution));
    $answers = array_combine(array_keys($solution), $wrong);
    $this->actingAs($this->student)->post(route('activities.attempts.submit', $first), ['answers' => $answers]);

    $second = startInteractiveAttempt($this, $activity, $this->student);
    $this->actingAs($this->student)->post(route('activities.attempts.submit', $second), ['answers' => $second->answers['solution']]);

    expect(Submission::first()->calificacion)->toBe('10.00');
});

it('no reemplaza la mejor calificación con un intento peor', function () {
    $activity = createInteractiveActivity($this->assignment, $this->teacher);
    $first = startInteractiveAttempt($this, $activity, $this->student);
    $this->actingAs($this->student)->post(route('activities.attempts.submit', $first), ['answers' => $first->answers['solution']]);

    $second = startInteractiveAttempt($this, $activity, $this->student);
    $solution = $second->answers['solution'];
    $answers = array_combine(array_keys($solution), array_reverse(array_values($solution)));
    $this->actingAs($this->student)->post(route('activities.attempts.submit', $second), ['answers' => $answers]);

    expect(Submission::first()->calificacion)->toBe('10.00');
});

it('impide jugar actividades en borrador o cerradas', function (string $status) {
    $activity = createInteractiveActivity($this->assignment, $this->teacher, [], ['estado' => $status]);

    $this->actingAs($this->student)
        ->post(route('activities.attempts.start', $activity))
        ->assertForbidden();
})->with(['borrador', 'cerrada']);

it('impide usar rutas interactivas con otro tipo de actividad', function () {
    $activity = Activity::factory()->for($this->assignment)->for($this->teacher, 'creator')->create(['tipo' => 'tarea']);

    $this->actingAs($this->student)
        ->post(route('activities.attempts.start', $activity))
        ->assertNotFound();
});

it('mantiene editable una relación de columnas existente', function () {
    $activity = createInteractiveActivity($this->assignment, $this->teacher);

    $this->actingAs($this->teacher)
        ->get(route('activities.edit', $activity))
        ->assertSuccessful()
        ->assertSee('México')
        ->assertSee('Ciudad de México');
});
