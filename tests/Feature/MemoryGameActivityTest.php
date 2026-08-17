<?php

use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Storage::fake('public');

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

it('crea un memorama duplicando tres imágenes en un intento', function () {
    $this->actingAs($this->teacher)->post(route('activities.store'), [
        'teaching_assignment_id' => $this->assignment->id,
        'titulo' => 'Memorama personal',
        'tipo' => 'memorama',
        'puntaje_maximo' => 10,
        'permite_entrega_tardia' => false,
        'estado' => 'publicada',
        'memory_items' => [
            ['label' => 'Familia', 'image' => UploadedFile::fake()->image('familia.png')],
            ['label' => 'Lectura', 'image' => UploadedFile::fake()->image('lectura.png')],
            ['label' => 'Deporte', 'image' => UploadedFile::fake()->image('deporte.png')],
        ],
        'max_attempts' => 2,
        'show_result_immediately' => true,
    ])->assertRedirect();

    $activity = Activity::with('content')->firstOrFail();
    $this->actingAs($this->student)->post(route('activities.memory.attempts.start', $activity))->assertRedirect();
    $attempt = $activity->attempts()->firstOrFail();

    expect($activity->content->configuracion['items'])->toHaveCount(3)
        ->and($attempt->answers['cards'])->toHaveCount(6)
        ->and(collect($attempt->answers['cards'])->groupBy('pair_id')->every(fn ($cards) => $cards->count() === 2))->toBeTrue();
});
