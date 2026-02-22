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
*/

// CRITICAL TEST ROUTE - This MUST be first to verify routing works
Route::get('/ping', function() {
    return response()->json([
        'success' => true,
        'message' => 'API routing is working!',
        'laravel_version' => app()->version(),
        'timestamp' => now()->toDateTimeString()
    ]);
});

// Test routes for CORS
Route::get('/cors-test', function() {
    return response()->json([
        'message' => 'CORS is configured correctly',
        'origin' => request()->header('Origin'),
        'method' => request()->method()
    ]);
});

Route::get('/test-cors', function() {
    return response()->json([
        'message' => 'CORS is working!',
        'timestamp' => now()->toDateTimeString()
    ]);
});

// PUBLIC ROUTES - No authentication required
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/verify/credential', [EmployerController::class, 'verifyCredential']);

// PROTECTED ROUTES - Require authentication
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Portfolio (All users)
    Route::prefix('portfolio')->group(function () {
        Route::get('/', [PortfolioController::class, 'index']);
        Route::put('/', [PortfolioController::class, 'update']);
        Route::post('/upload-photo', [PortfolioController::class, 'uploadPhoto']);
        
        // Portfolio items
        Route::post('/items', [PortfolioController::class, 'addItem']);
        Route::put('/items/{id}', [PortfolioController::class, 'updateItem']);
        Route::delete('/items/{id}', [PortfolioController::class, 'deleteItem']);
    });

    // Institution routes - FIXED: comma instead of pipe
    Route::middleware('role:institution,admin')->prefix('institution')->group(function () {
        Route::get('/dashboard', [InstitutionDashboardController::class, 'index']);
        Route::get('/verifications', [VerificationController::class, 'queue']);
        Route::get('/verifications/{verification}', [VerificationController::class, 'show']);
        Route::post('/verifications/{verification}/start', [VerificationController::class, 'startReview']);
        Route::post('/verifications/{verification}/approve', [VerificationController::class, 'approve']);
        Route::post('/verifications/{verification}/reject', [VerificationController::class, 'reject']);
        Route::post('/verifications/{verification}/revoke', [VerificationController::class, 'revoke']);
    });

    // Employer routes - FIXED: comma instead of pipe
    Route::middleware('role:employer,admin')->prefix('employer')->group(function () {
        Route::get('/dashboard', [EmployerController::class, 'dashboard']);
        Route::get('/candidates', [EmployerController::class, 'searchCandidates']);
        Route::get('/candidates/{id}', [EmployerController::class, 'viewCandidateProfile']);
    });

    Route::get('/debug-middleware', function() {
    $router = app('router');
    $routeMiddleware = $router->getMiddleware();
    
    return response()->json([
        'registered_middleware' => array_keys($routeMiddleware),
        'role_middleware_class' => $routeMiddleware['role'] ?? 'not found',
        'role_middleware_exists' => isset($routeMiddleware['role']),
    ]);
});

    // Admin routes
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/institutions', [AdminController::class, 'institutions']);
        Route::post('/institutions/{id}/approve', [AdminController::class, 'approveInstitution']);
        Route::post('/institutions/{id}/reject', [AdminController::class, 'rejectInstitution']);
        Route::get('/audit-logs', [AdminController::class, 'auditLogs']);
        Route::post('/users/{id}/suspend', [AdminController::class, 'suspendUser']);
        Route::post('/users/{id}/reactivate', [AdminController::class, 'reactivateUser']);
    });
});