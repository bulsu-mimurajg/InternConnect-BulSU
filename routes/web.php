<?php

use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\AssessmentController;
use App\Models\Question;
use App\Models\SubCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::get('/about', function () {
    return Inertia::render('about');
})->name('about');

Route::get('/contact', function () {
    return Inertia::render('contact');
})->name('contact');

Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::get('student/list', [StudentController::class, 'index'])->name('student-list');

    Route::get('student/matched', function () {
        return Inertia::render('admin/student/matched');
    })->name('student-matched');

    Route::get('student/placed', function () {
        return Inertia::render('admin/student/placed');
    })->name('student-placed');

    Route::get('hte', function () {
        return Inertia::render('admin/hte');
    })->name('hte');

    Route::get('placement', function () {
        return Inertia::render('admin/placement');
    })->name('placement');

    Route::get('report', function () {
        return Inertia::render('admin/report');
    })->name('report');
});

Route::middleware(['auth', 'verified', 'role:hte'])->group(function () {
    Route::get('form', function () {
        return Inertia::render('hte/form');
    })->name('form');
});

Route::group(['middleware' => ['auth', 'verified', 'role:student']], function () {
    Route::get('assessment', function () {
//        $subcategories = SubCategory::with(['questions' => function ($query) {
//            $query->where('access', 'student');
//        }])->get();

        // Check if the authenticated user's student record has already submitted the assessment
        $student = Auth::user()->student;
        $hasSubmitted = $student ? $student->is_submit : false;

//        $questions = Question::all()->where('access', 'student');
        return Inertia::render('student/assessment', [
            'hasSubmitted' => $hasSubmitted
        ]);
    })->name('assessment');

    Route::post('assessment', [AssessmentController::class, 'store'])->name('assessment.store');
    Route::get('assessment/language-proficiency', [AssessmentController::class, 'getLanguageProficiency'])->name('assessment.language-proficiency');
    Route::get('assessment/technical-skills', [AssessmentController::class, 'getTechnicalSkills'])->name('assessment.technical-skills');
    Route::get('assessment/soft-skills', [AssessmentController::class, 'getSoftSkills'])->name('assessment.soft-skills');
});


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
