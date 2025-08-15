<?php

use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AdviserController;
use App\Http\Controllers\HTEController;
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
    Route::get('form', [App\Http\Controllers\HTEController::class, 'showForm'])->name('form');
    Route::post('hte/submit', [App\Http\Controllers\HTEController::class, 'submit'])->name('hte.submit');
    Route::get('hte/categories', [App\Http\Controllers\HTEController::class, 'getCategoriesForCriteria'])->name('hte.categories');
    Route::get('hte/profile', [App\Http\Controllers\HTEController::class, 'profile'])->name('hte.profile');
    Route::get('hte/check-existing', [App\Http\Controllers\HTEController::class, 'checkExistingHTE'])->name('hte.check-existing');
});



Route::middleware(['auth', 'verified', 'role:adviser'])->group(function () {
    Route::get('application', [AdviserController::class, 'index'])->name('application');
    Route::post('application/approve', [AdviserController::class, 'approveStudents'])->name('application.approve');
    Route::post('application/reject', [AdviserController::class, 'rejectStudents'])->name('application.reject');
    Route::post('application/remove-access', [AdviserController::class, 'removeStudentAccess'])->name('application.remove-access');
    Route::post('application/undo', [AdviserController::class, 'undoAction'])->name('application.undo');
});

Route::group(['middleware' => ['auth', 'verified', 'role:student']], function () {
    Route::get('assessment', function () {
        $subcategories = SubCategory::with(['questions' => function ($query) {
            $query->where('access', 'student');
        }])->get();

        // Check if the authenticated user's student record has already submitted the assessment
        $student = Auth::user()->student;
        $hasSubmitted = $student ? $student->is_submit : false;

//        $questions = Question::all()->where('access', 'student');
        return Inertia::render('student/assessment', [
            'subcategories' => $subcategories,
            'hasSubmitted' => $hasSubmitted
        ]);
    })->name('assessment');

    Route::get('student-profile', function () {
        $user = Auth::user();
        $student = $user->student;
        
        if (!$student) {
            return Inertia::render('student/profile', [
                'student' => null,
                'categories' => []
            ]);
        }

        // Check if student has submitted the assessment
        if (!$student->is_submit) {
            return Inertia::render('student/profile', [
                'student' => $student,
                'categories' => [],
                'hasSubmitted' => false
            ]);
        }

        // Get all categories with their subcategories and scores
        $categories = \App\Models\Category::with(['subCategory' => function ($query) use ($student) {
            $query->with(['studentScores' => function ($scoreQuery) use ($student) {
                $scoreQuery->where('student_id', $student->id);
            }]);
        }])->get();

        // Transform the data to match frontend expectations
        $transformedCategories = $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->category_name,
                'subcategories' => $category->subCategory->map(function ($subcategory) {
                    $score = $subcategory->studentScores->first();
                    return [
                        'id' => $subcategory->id,
                        'name' => $subcategory->subcategory_name,
                        'score' => $score ? round($score->score, 2) : 0,
                    ];
                })->toArray()
            ];
        })->toArray();

        return Inertia::render('student/profile', [
            'student' => $student,
            'categories' => $transformedCategories,
            'hasSubmitted' => true
        ]);
    })->name('profile');

    Route::post('assessment', [AssessmentController::class, 'store'])->name('assessment.store');
    Route::get('assessment/language-proficiency', [AssessmentController::class, 'getLanguageProficiency'])->name('assessment.language-proficiency');
    Route::get('assessment/technical-skills', [AssessmentController::class, 'getTechnicalSkills'])->name('assessment.technical-skills');
    Route::get('assessment/soft-skills', [AssessmentController::class, 'getSoftSkills'])->name('assessment.soft-skills');
});

Route::get('/api/categories-with-subcategories', function () {
    $categories = \App\Models\Category::with(['subCategory.questions' => function($query) {
        $query->where('is_active', true);
    }])->get();
    
    return response()->json($categories);
});


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
