<?php

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
    Route::get('student', function () {
        return Inertia::render('admin/student');
    })->name('student');
});

Route::middleware(['auth', 'verified', 'role:hte'])->group(function () {
    Route::get('form', function () {
        return Inertia::render('hte/form');
    })->name('form');
});

Route::group(['middleware' => ['auth', 'verified', 'role:student']], function () {
    Route::get('assessment', function () {
        return Inertia::render('student/assessment');
    })->name('assessment');
});


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
