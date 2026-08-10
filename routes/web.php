<?php

use App\Http\Controllers\AcademicPeriodController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\InteractiveActivityController;
use App\Http\Controllers\SchoolGradeController;
use App\Http\Controllers\SchoolGroupController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\TeachingAssignmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WordSearchController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

/*Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});*/
Route::view('/dashboard', 'dashboard')
    ->middleware('auth')
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('activities/{activity}/word-search', [WordSearchController::class, 'play'])
        ->name('activities.word-search.play');
    Route::post('activities/{activity}/word-search/attempts', [WordSearchController::class, 'startAttempt'])
        ->name('activities.word-search.attempts.start');
    Route::get('activities/{activity}/word-search/attempts', [WordSearchController::class, 'attempts'])
        ->name('activities.word-search.attempts.index');
    Route::post('word-search-attempts/{attempt}/submit', [WordSearchController::class, 'submitAttempt'])
        ->name('activities.word-search.attempts.submit');
    Route::get('word-search-attempts/{attempt}/result', [WordSearchController::class, 'result'])
        ->name('activities.word-search.attempts.result');

    Route::get('activities/{activity}/play', [InteractiveActivityController::class, 'play'])
        ->name('activities.play');
    Route::post('activities/{activity}/attempts', [InteractiveActivityController::class, 'startAttempt'])
        ->name('activities.attempts.start');
    Route::get('activities/{activity}/attempts', [InteractiveActivityController::class, 'attempts'])
        ->name('activities.attempts.index');
    Route::post('activity-attempts/{attempt}/submit', [InteractiveActivityController::class, 'submitAttempt'])
        ->name('activities.attempts.submit');
    Route::get('activity-attempts/{attempt}/result', [InteractiveActivityController::class, 'result'])
        ->name('activities.attempts.result');

    Route::get('activities/{activity}/download', [ActivityController::class, 'download'])
        ->name('activities.download');
    Route::resource('activities', ActivityController::class);

    Route::get('submissions/{submission}/grade', [SubmissionController::class, 'grade'])
        ->name('submissions.grade');
    Route::match(['put', 'patch'], 'submissions/{submission}/grade', [SubmissionController::class, 'updateGrade'])
        ->name('submissions.update-grade');
    Route::get('submissions/{submission}/download', [SubmissionController::class, 'download'])
        ->name('submissions.download');
    Route::resource('submissions', SubmissionController::class);
});

Route::middleware([
    'auth',
    'role:administrador',

])->group(function () {
    Route::resource(
        'academic-periods',
        AcademicPeriodController::class
    );

    Route::resource(
        'school-grades',
        SchoolGradeController::class
    );

    Route::resource(
        'school-groups',
        SchoolGroupController::class
    );
    Route::resource(
        'subjects',
        SubjectController::class
    );
    Route::resource('users', UserController::class);
    Route::resource(
        'enrollments',
        EnrollmentController::class
    );

    Route::resource(
        'teaching-assignments',
        TeachingAssignmentController::class
    );
});

require __DIR__.'/settings.php';
