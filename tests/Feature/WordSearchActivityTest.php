<?php

use App\Models\Activity;
use App\Models\ActivityAttempt;
use App\Models\ActivityContent;
use App\Models\Enrollment;
use App\Models\Submission;
use App\Models\TeachingAssignment;
use App\Models\User;
use App\Services\WordSearchGenerator;
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

function wordSearchActivityData(TeachingAssignment $assignment, array $overrides = []): array
{
    return [
        'teaching_assignment_id' => $assignment->id,
        'titulo' => 'Elementos de la célula',
        'tipo' => 'sopa_letras',
        'fecha_publicacion' => now()->subMinute()->format('Y-m-d H:i:s'),
        'fecha_limite' => now()->addWeek()->format('Y-m-d H:i:s'),
        'puntaje_maximo' => 10,
        'permite_entrega_tardia' => false,
        'estado' => 'publicada',
        'words' => [['text' => 'Oxígeno'], ['text' => 'Carbono'], ['text' => 'Célula']],
        'word_search_rows' => 12,
        'word_search_columns' => 12,
        'word_search_directions' => ['horizontal', 'vertical', 'diagonal_down'],
        'allow_reverse' => true,
        'max_attempts' => 2,
        'show_result_immediately' => true,
        ...$overrides,
    ];
}

function createWordSearchActivity(TeachingAssignment $assignment, User $teacher, array $configuration = []): Activity
{
    $activity = Activity::factory()->for($assignment)->for($teacher, 'creator')->create(['tipo' => 'sopa_letras']);
    $generated = app(WordSearchGenerator::class)->generate(
        ['Oxígeno', 'Carbono', 'Célula'], 12, 12, ['horizontal', 'vertical', 'diagonal_down'], true
    );
    ActivityContent::factory()->for($activity)->create(['configuracion' => [
        ...$generated,
        'max_attempts' => 2,
        'show_result_immediately' => true,
        ...$configuration,
    ]]);

    return $activity;
}

function startWordSearchAttempt($test, Activity $activity, User $student): ActivityAttempt
{
    $test->actingAs($student)->post(route('activities.word-search.attempts.start', $activity))->assertRedirect();

    return ActivityAttempt::query()->latest('id')->firstOrFail();
}

it('permite al docente crear una sopa con ids y cuadrícula generados en el servidor', function () {
    $this->actingAs($this->teacher)->post(route('activities.store'), wordSearchActivityData($this->assignment))->assertRedirect();
    $configuration = Activity::with('content')->firstOrFail()->content->configuracion;

    expect($configuration['words'])->toHaveCount(3)
        ->and($configuration['words'][0]['normalized'])->toBe('OXIGENO')
        ->and($configuration['words'][0]['id'])->not->toBeEmpty()
        ->and($configuration['grid'])->toHaveCount(12)
        ->and($configuration['placements'])->toHaveCount(3);
});

it('valida palabras normalizadas duplicadas', function () {
    $this->actingAs($this->teacher)->post(route('activities.store'), wordSearchActivityData($this->assignment, [
        'words' => [['text' => 'Oxígeno'], ['text' => 'oxigeno'], ['text' => 'Célula']],
    ]))->assertSessionHasErrors('words');
});

it('no expone posiciones ni coordenadas correctas al estudiante', function () {
    $activity = createWordSearchActivity($this->assignment, $this->teacher);
    $attempt = startWordSearchAttempt($this, $activity, $this->student);
    $this->actingAs($this->student)
        ->get(route('activities.word-search.attempts.result', $attempt))
        ->assertSuccessful()
        ->assertViewHas('publicConfiguration', fn (array $public) => ! isset($public['placements'])
            && collect($public['words'])->every(fn (array $word) => ! isset($word['normalized'])));
});

it('calcula la puntuación en el servidor e ignora la enviada por el cliente', function () {
    $activity = createWordSearchActivity($this->assignment, $this->teacher);
    $attempt = startWordSearchAttempt($this, $activity, $this->student);
    $placement = $activity->content->configuracion['placements'][0];

    $this->actingAs($this->student)->post(route('activities.word-search.attempts.submit', $attempt), [
        'selections' => [[
            'start_row' => $placement['start_row'], 'start_column' => $placement['start_column'],
            'end_row' => $placement['end_row'], 'end_column' => $placement['end_column'],
        ]],
        'score' => 10,
    ])->assertRedirect();

    expect($attempt->refresh()->score)->toBe('3.33')
        ->and(Submission::first()->calificacion)->toBe('3.33');
});

it('reconoce una palabra seleccionada desde cualquiera de sus extremos', function () {
    $activity = createWordSearchActivity($this->assignment, $this->teacher, ['allow_reverse' => false]);
    $attempt = startWordSearchAttempt($this, $activity, $this->student);
    $placement = $activity->content->configuracion['placements'][0];

    $this->actingAs($this->student)->post(route('activities.word-search.attempts.submit', $attempt), [
        'selections' => [[
            'start_row' => $placement['end_row'], 'start_column' => $placement['end_column'],
            'end_row' => $placement['start_row'], 'end_column' => $placement['start_column'],
        ]],
    ])->assertRedirect();

    expect($attempt->refresh()->answers['correct_count'])->toBe(1)
        ->and($attempt->score)->toBe('3.33');
});

it('reconoce el valor correcto aunque aparezca fuera de la ubicación generada originalmente', function () {
    $activity = createWordSearchActivity($this->assignment, $this->teacher);
    $configuration = $activity->content->configuracion;
    $grid = $configuration['grid'];
    $grid[11] = [...mb_str_split('OXIGENO'), ...array_fill(0, 5, 'Z')];
    $activity->content->update(['configuracion' => [...$configuration, 'grid' => $grid]]);
    $attempt = startWordSearchAttempt($this, $activity, $this->student);

    $this->actingAs($this->student)->post(route('activities.word-search.attempts.submit', $attempt), [
        'selections' => [[
            'start_row' => 11, 'start_column' => 0,
            'end_row' => 11, 'end_column' => 6,
        ]],
    ])->assertRedirect();

    expect($attempt->refresh()->answers['correct_count'])->toBe(1)
        ->and($attempt->score)->toBe('3.33');
});

it('rechaza coordenadas fuera de la cuadrícula y selecciones no rectas', function (array $selection) {
    $activity = createWordSearchActivity($this->assignment, $this->teacher);
    $attempt = startWordSearchAttempt($this, $activity, $this->student);

    $this->actingAs($this->student)->post(route('activities.word-search.attempts.submit', $attempt), [
        'selections' => [$selection],
    ])->assertSessionHasErrors();
})->with([
    [['start_row' => -1, 'start_column' => 0, 'end_row' => 1, 'end_column' => 0]],
    [['start_row' => 0, 'start_column' => 0, 'end_row' => 2, 'end_column' => 1]],
]);

it('impide que otro estudiante envíe el intento', function () {
    $activity = createWordSearchActivity($this->assignment, $this->teacher);
    $attempt = startWordSearchAttempt($this, $activity, $this->student);
    $outsider = User::factory()->create();
    $outsider->assignRole('estudiante');

    $this->actingAs($outsider)->post(route('activities.word-search.attempts.submit', $attempt), [
        'selections' => [['start_row' => 0, 'start_column' => 0, 'end_row' => 0, 'end_column' => 1]],
    ])->assertForbidden();
});

it('impide cambiar la estructura cuando ya hay intentos', function () {
    $activity = createWordSearchActivity($this->assignment, $this->teacher);
    startWordSearchAttempt($this, $activity, $this->student);

    $this->actingAs($this->teacher)->put(route('activities.update', $activity), wordSearchActivityData($this->assignment, [
        'words' => [['text' => 'Oxígeno'], ['text' => 'Carbono'], ['text' => 'Mitocondria']],
    ]))->assertSessionHasErrors('tipo');
});
