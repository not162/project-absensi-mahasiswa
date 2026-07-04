<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/attendance/latest', [\App\Http\Controllers\Api\AttendanceController::class, 'pollLatest'])->name('api.attendance.latest');
    
    Route::get('/assignments/{assignment}/submissions', [\App\Http\Controllers\Api\AssignmentSubmissionController::class, 'index'])->name('api.assignments.submissions');
    
    Route::apiResource('assignments.discussions', \App\Http\Controllers\Api\DiscussionController::class)
        ->only(['index', 'store', 'update', 'destroy']);
});
