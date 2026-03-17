<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\EmployerController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\InstitutionDashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| This file contains the national skills verification platform API routes.
| Routes are organized by authentication requirements and user roles.
|
*/

// Health Check
Route::get('/health', function() {
    return response()->json([
        'status' => 'operational',
        'timestamp' => now()->toDateTimeString(),
        'version' => '1.0.0'
    ]);
});

// PUBLIC ROUTES
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// PROTECTED ROUTES
Route::middleware('auth:sanctum')->group(function () {
    // Identity & Session
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Professional Portfolio
    Route::prefix('portfolio')->group(function () {
        Route::get('/', [PortfolioController::class, 'index']);
        Route::put('/', [PortfolioController::class, 'update']);
        Route::post('/photo', [PortfolioController::class, 'uploadPhoto']);
        
        Route::prefix('items')->group(function () {
            Route::post('/', [PortfolioController::class, 'addItem']);
            Route::put('/{id}', [PortfolioController::class, 'updateItem']);
            Route::delete('/{id}', [PortfolioController::class, 'deleteItem']);
        });
    });

    // Institution Verification Services
    Route::middleware('role:institution,admin')->prefix('institution')->group(function () {
        Route::get('/dashboard', [InstitutionDashboardController::class, 'index']);
        Route::prefix('verifications')->group(function () {
            Route::get('/', [VerificationController::class, 'queue']);
            Route::get('/{verification}', [VerificationController::class, 'show']);
            Route::post('/{verification}/start', [VerificationController::class, 'startReview']);
            Route::post('/{verification}/approve', [VerificationController::class, 'approve']);
            Route::post('/{verification}/reject', [VerificationController::class, 'reject']);
            Route::post('/{verification}/revoke', [VerificationController::class, 'revoke']);
        });
    });

    // Employer Talent Services
    Route::middleware('role:employer,admin')->prefix('employer')->group(function () {
        Route::get('/dashboard', [EmployerController::class, 'dashboard']);
        Route::get('/candidates', [EmployerController::class, 'searchCandidates']);
        Route::get('/candidates/{id}', [EmployerController::class, 'viewCandidateProfile']);
        Route::get('/verify-credential', [EmployerController::class, 'verifyCredential']);
    });

    // System Administration
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/audit-logs', [AdminController::class, 'auditLogs']);
        
        Route::prefix('institutions')->group(function () {
            Route::get('/', [AdminController::class, 'institutions']);
            Route::post('/{id}/approve', [AdminController::class, 'approveInstitution']);
            Route::post('/{id}/reject', [AdminController::class, 'rejectInstitution']);
        });

        Route::prefix('users')->group(function () {
            Route::post('/{id}/suspend', [AdminController::class, 'suspendUser']);
            Route::post('/{id}/reactivate', [AdminController::class, 'reactivateUser']);
        });
    });
});