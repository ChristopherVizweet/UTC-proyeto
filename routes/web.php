<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AcademicPeriodController;
use App\Http\Controllers\SchoolGradeController;
use App\Http\Controllers\SchoolGroupController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\UserController;

Route::view('/', 'welcome')->name('home');

/*Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});*/
Route::view('/dashboard', 'dashboard')
    ->middleware('auth')
    ->name('dashboard');

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
});

require __DIR__.'/settings.php';
