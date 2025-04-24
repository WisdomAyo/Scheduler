<?php
// routes/api.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\EventController;
use App\Http\Controllers\Api\v1\RegistrationController;
use App\Http\Controllers\Api\v1\ParticipantController;

// Group routes for v1
Route::prefix('v1')->group(function () {
    // Event Routes
    Route::post('/events', [EventController::class, 'store']);
    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{event}', [EventController::class, 'show']); // Route model binding

    // Registration Route (Example - needs RegistrationController)
    Route::post('/events/{event}/register', [RegistrationController::class, 'store']);

    // Add other routes (participant registrations etc.)
    Route::get('/participants/{participant}/registrations', [ParticipantController::class, 'registrations'])->name('participants.registrations');
});
