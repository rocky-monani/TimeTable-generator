<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\FacultyAvailabilityController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SubjectLabController;
use App\Http\Controllers\SectionRoomController;
use App\Http\Controllers\RuleController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GeneratorController;
use App\Http\Controllers\TimetableController;
// use App\Http\Controllers\TimetableLockController;



/*
| Web Routes
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate']);
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/', function () {
        return view('dashboard');
    })->name('home');
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    Route::get('/home', function () {
        return view('dashboard');
    })->name('home');

    /*
    |--------------------------------------------------------------------------
    | Department CRUD Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/departments', [DepartmentController::class, 'index']);
    Route::get('/departments/list', [DepartmentController::class, 'list']);
    Route::post('/departments/store', [DepartmentController::class, 'store']);
    Route::get('/departments/edit/{id}', [DepartmentController::class, 'edit']);
    Route::post('/departments/update/{id}', [DepartmentController::class, 'update']);
    Route::delete('/departments/delete/{id}', [DepartmentController::class, 'delete']);

    /*
    |--------------------------------------------------------------------------
    | Faculty CRUD Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/faculty', [FacultyController::class, 'index']);
    Route::get('/faculty/list', [FacultyController::class, 'list']);
    Route::post('/faculty/store', [FacultyController::class, 'store']);
    Route::get('/faculty/edit/{id}', [FacultyController::class, 'edit']);
    Route::delete('/faculty/delete/{id}', [FacultyController::class, 'delete']);

    /*
    |--------------------------------------------------------------------------
    | Section CRUD Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/sections', [SectionController::class, 'index']);
    Route::get('/sections/list', [SectionController::class, 'list']);
    Route::post('/sections/store', [SectionController::class, 'store']);
    Route::get('/sections/edit/{id}', [SectionController::class, 'edit']);
    Route::post('/sections/update/{id}', [SectionController::class, 'update']);
    Route::delete('/sections/delete/{id}', [SectionController::class, 'delete']);

    /*
    |--------------------------------------------------------------------------
    | Subject CRUD Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/subjects', [SubjectController::class, 'index']);
    Route::get('/subjects/list', [SubjectController::class, 'list']);
    Route::post('/subjects/store', [SubjectController::class, 'store']);
    Route::get('/subjects/edit/{id}', [SubjectController::class, 'edit']);
    Route::post('/subjects/update/{id}', [SubjectController::class, 'update']);
    Route::delete('/subjects/delete/{id}', [SubjectController::class, 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | Faculty-availability CRUD Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/faculty-availability', [FacultyAvailabilityController::class, 'index']);
    Route::post('/faculty-availability/save', [FacultyAvailabilityController::class, 'save']);

    // Real-time toggle route for instant availability updates
    Route::post('/faculty-availability/toggle', [FacultyAvailabilityController::class, 'toggleAvailability']);

    /*
    |--------------------------------------------------------------------------
    | Room CRUD Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/rooms', [RoomController::class, 'index']);
    Route::get('/rooms/list', [RoomController::class, 'list']);
    Route::post('/rooms/store', [RoomController::class, 'store']);
    Route::get('/rooms/edit/{id}', [RoomController::class, 'edit']);
    Route::delete('/rooms/delete/{id}', [RoomController::class, 'delete']);

    /*
    |--------------------------------------------------------------------------
    | Subject-lab CRUD Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/subject-labs', [SubjectLabController::class, 'index']);
    Route::post('/subject-labs/save', [SubjectLabController::class, 'save']);
    Route::get('/subject-labs/list', [SubjectLabController::class, 'list']);

    /*
    |--------------------------------------------------------------------------
    | Section-Room CRUD Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/section-rooms', [SectionRoomController::class, 'index']);
    Route::post('/section-rooms/save', [SectionRoomController::class, 'save']);
    Route::get('/section-rooms/list', [SectionRoomController::class, 'list']);

    /*
    |--------------------------------------------------------------------------
    | Section-Room CRUD Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/rules', [RuleController::class, 'index']);
    Route::get('/rules/list', [RuleController::class, 'list']);
    Route::post('/rules/store', [RuleController::class, 'store']);
    Route::get('/rules/edit/{id}', [RuleController::class, 'edit']);
    Route::delete('/rules/delete/{id}', [RuleController::class, 'delete']);
    Route::get('/excel-template', [ImportController::class, 'template']);
    Route::get('/excel-import', [ImportController::class, 'index']);
    Route::post('/excel-import', [ImportController::class, 'import']);
    
    Route::get('/import/paste', [ImportController::class, 'pasteView']);
    Route::post('/import/paste', [ImportController::class, 'processPaste']);
    Route::get('/generate-timetable', function () {
        return "Timetable generator coming soon";
    });
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/', [DashboardController::class, 'index']);


    Route::get('/generate', [GeneratorController::class, 'generate']);

    Route::middleware(['auth'])->group(function () {

        Route::get('/faculty-availability', [FacultyAvailabilityController::class, 'index'])
            ->name('faculty.availability');

        Route::post('/faculty-availability/toggle', [FacultyAvailabilityController::class, 'toggleAvailability'])
            ->name('faculty.availability.toggle');

    });



    // Timetable view
    Route::get('/timetable', [TimetableController::class, 'index']);
    Route::get('/timetable/data/{id}', [TimetableController::class, 'getData']);
    
    // Manual Edit
    Route::get('/timetable/edit/{id}', [TimetableController::class, 'edit']);
    Route::get('/timetable/subjects/{section_id}', [TimetableController::class, 'getAvailableSubjects']);
    Route::post('/timetable/update-slot', [TimetableController::class, 'updateSlot']);
    Route::get('/timetable/print/{id}', [TimetableController::class, 'print']);

    // Generator trigger
    Route::get('/generate', [GeneratorController::class, 'generate']);

    /*
    Route::get('/timetable-lock', [TimetableLockController::class, 'index']);
    Route::post('/timetable-lock/check', [TimetableLockController::class, 'checkConflict']);
    Route::post('/timetable-lock/store', [TimetableLockController::class, 'store']);
    Route::post('/timetable-lock/toggle', [TimetableLockController::class, 'toggle']);
    */

    Route::post('/timetable/reset', [TimetableController::class, 'reset']);
});