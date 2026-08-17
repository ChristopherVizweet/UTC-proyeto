<?php

use App\Models\Activity;
use App\Models\ActivityAttempt;
use App\Models\Enrollment;
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

function hayAhiAyData(TeachingAssignment $assignment): array
{
    return [
        'teaching_assignment_id' => $assignment->id,
        'titulo' => '¿Hay, ahí o ay?',
        'tipo' => 'hay_ahi_ay',
        'fecha_publicacion' => now()->subMinute()->format('Y-m-d H:i:s'),
        'fecha_limite' => now()->addWeek()->format('Y-m-d H:i:s'),
        'puntaje_maximo' => 10,
        'permite_entrega_tardia' => false,
        'estado' => 'publicada',
        'hay_ahi_ay_items' => [
            ['sentence' => '___ suficiente comida.', 'answer' => 'hay'],
            ['sentence' => 'Deja el libro ___.', 'answer' => 'ahí'],
            ['sentence' => '¡___, me lastimé!', 'answer' => 'ay'],
        ],
        'max_attempts' => 2,
        'show_result_immediately' => true,
    ];
}

it('crea y califica la actividad hay ahí o ay', function () {
    $this->actingAs($this->teacher)->post(route('activities.store'), hayAhiAyData($this->assignment))->assertRedirect();
    $activity = Activity::with('content')->firstOrFail();
    $this->actingAs($this->student)->post(route('activities.hay-ahi-ay.attempts.start', $activity))->assertRedirect();
    $attempt = ActivityAttempt::firstOrFail();

    $this->actingAs($this->student)->post(route('activities.hay-ahi-ay.attempts.submit', $attempt), [
        'answers' => $attempt->answers['solution'],
    ])->assertRedirect();

    expect($activity->tipo)->toBe('hay_ahi_ay')
        ->and($attempt->refresh()->score)->toBe('10.00')
        ->and($attempt->answers['correct_count'])->toBe(3);
});

it('exige que cada oración incluya el espacio en blanco', function () {
    $data = hayAhiAyData($this->assignment);
    $data['hay_ahi_ay_items'][0]['sentence'] = 'Hay suficiente comida.';

    $this->actingAs($this->teacher)->post(route('activities.store'), $data)
        ->assertSessionHasErrors('hay_ahi_ay_items.0.sentence');
});
