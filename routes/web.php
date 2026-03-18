<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\BoundaryController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\CodingController;
use App\Http\Controllers\DriverBehaviorController;
use App\Http\Controllers\DriverManagementController;
use App\Http\Controllers\OfficeExpenseController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\LiveTrackingController;
use App\Http\Controllers\UnitProfitabilityController;
use App\Http\Controllers\DecisionManagementController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\GitHubAuthController;
use App\Http\Controllers\GitHubIntegrationController;

// ─── Auth Routes ───────────────────────────────────────
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// ─── GitHub OAuth Routes ───────────────────────────────
Route::get('/auth/github', [GitHubAuthController::class, 'redirectToGitHub'])->name('auth.github');
Route::get('/auth/github/callback', [GitHubAuthController::class, 'handleGitHubCallback'])->name('auth.github.callback');

// Real-time dashboard data
Route::get('/api/dashboard/realtime', [DashboardController::class, 'getRealTimeData'])->middleware('auth');
Route::get('/api/revenue-trend', [DashboardController::class, 'getRevenueTrend'])->middleware('auth');
Route::get('/api/units-overview', [DashboardController::class, 'getUnitsOverview'])->middleware('auth');

// Auto Reload Route
Route::get('/check-changes', function() {
    $lastModifiedFile = storage_path('framework/cache/last-modified.txt');
    $currentModified = filemtime($lastModifiedFile) ?? 0;
    
    // Update last modified time
    file_put_contents($lastModifiedFile, time());
    
    return response()->json([
        'changed' => false,
        'timestamp' => $currentModified
    ]);
});

    // ─── Protected Routes ──────────────────────────────────
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Units Resource Routes
    Route::resource('units', UnitController::class);
    Route::get('/units/details', [UnitController::class, 'getDetails'])->name('units.details');
    Route::post('/units/toggle-status', [UnitController::class, 'toggleStatus'])->name('units.toggle-status');
    Route::get('/units/import', [UnitController::class, 'showImport'])->name('units.import');
    Route::post('/units/import', [UnitController::class, 'import'])->name('units.import.store');

    // Boundaries Resource Routes
    Route::resource('boundaries', BoundaryController::class);

    // Maintenance Resource Routes
    Route::resource('maintenance', MaintenanceController::class);

    // Coding Management
    Route::get('/coding', [CodingController::class, 'index'])->name('coding.index');
    Route::resource('coding-rules', CodingController::class)->except(['show', 'edit']);
    Route::post('/coding/update-day', [CodingController::class, 'updateCodingDay'])->name('coding.update-day');

    // Driver Behavior Resource Routes
    Route::resource('driver-behavior', DriverBehaviorController::class);
    Route::get('/driver-behavior/statistics', [DriverBehaviorController::class, 'getStatistics'])->name('driver-behavior.statistics');

    // Driver Management Resource Routes
    Route::resource('driver-management', DriverManagementController::class);
    Route::post('/driver-management/upload-documents/{id}', [DriverManagementController::class, 'uploadDocuments'])->name('driver-management.upload-documents');

    // Office Expenses Resource Routes
    Route::resource('office-expenses', OfficeExpenseController::class);
    Route::post('/office-expenses/approve/{id}', [OfficeExpenseController::class, 'approve'])->name('office-expenses.approve');
    Route::post('/office-expenses/reject/{id}', [OfficeExpenseController::class, 'reject'])->name('office-expenses.reject');

    // Salary Management
    Route::get('/salary', [SalaryController::class, 'index'])->name('salary.index');
    Route::resource('salaries', SalaryController::class)->only(['store', 'update', 'destroy']);
    Route::get('/salary/report', [SalaryController::class, 'monthlyReport'])->name('salary.report');

    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

    // Live Tracking
    Route::get('/live-tracking', [LiveTrackingController::class, 'index'])->name('live-tracking.index');
    Route::get('/live-tracking/unit/{id}', [LiveTrackingController::class, 'getUnitLocation'])->name('live-tracking.unit-location');

    // Unit Profitability
    Route::get('/unit-profitability', [UnitProfitabilityController::class, 'index'])->name('unit-profitability.index');

    // Decision Management Resource Routes
    Route::resource('decision-management', DecisionManagementController::class);
    Route::post('/decision-management/approve/{id}', [DecisionManagementController::class, 'approve'])->name('decision-management.approve');
    Route::post('/decision-management/reject/{id}', [DecisionManagementController::class, 'reject'])->name('decision-management.reject');

    // Notifications (AJAX)
    Route::post('/notifications/dismiss', [NotificationController::class, 'dismissAlert'])->name('notifications.dismiss');

    // ─── GitHub Integration Routes ─────────────────────
    Route::get('/github', [GitHubIntegrationController::class, 'index'])->name('github.index');
    Route::get('/api/github/stats', [GitHubIntegrationController::class, 'getStats'])->name('github.stats');
    Route::get('/api/github/commits', [GitHubIntegrationController::class, 'getCommits'])->name('github.commits');
    Route::get('/api/github/pulls', [GitHubIntegrationController::class, 'getPullRequests'])->name('github.pulls');
    Route::get('/api/github/issues', [GitHubIntegrationController::class, 'getIssues'])->name('github.issues');
    Route::post('/api/github/issue', [GitHubIntegrationController::class, 'createIssue'])->name('github.create-issue');
    Route::get('/api/github/contributors', [GitHubIntegrationController::class, 'getContributors'])->name('github.contributors');
    Route::get('/api/github/workflow/{workflowId}', [GitHubIntegrationController::class, 'getWorkflowStatus'])->name('github.workflow-status');
    Route::post('/api/github/workflow/trigger', [GitHubIntegrationController::class, 'triggerWorkflow'])->name('github.trigger-workflow');
});
