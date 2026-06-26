<?php

use App\Http\Controllers\Admin\AdminApplicationController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminJobController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminWorkerController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminCityController;
use App\Http\Controllers\Admin\AdminSkillController;
use App\Http\Controllers\Admin\AdminIndustryController;
use App\Http\Controllers\Admin\AdminLanguageController;
use App\Http\Controllers\Admin\AdminNationalityController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

// ── Password Reset Web Routes ──────────────────────────────
Route::get('/password/reset/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [AuthController::class, 'resetPassword'])->name('password.update');

// ── Admin Panel Routes ─────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {
    // Auth Routes
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    // Protected Routes
    Route::middleware(['auth', 'is_admin'])->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Users
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::put('/users/{user}/verification', [AdminUserController::class, 'updateVerification'])->name('users.updateVerification');
    Route::put('/users/{user}/worker-verification', [AdminUserController::class, 'updateWorkerVerification'])->name('users.updateWorkerVerification');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    // Worker Management (Badge + Documents)
    Route::prefix('workers')->name('workers.')->group(function () {
        Route::get('/',                                           [AdminWorkerController::class, 'index'])->name('index');
        Route::get('/{user}',                                    [AdminWorkerController::class, 'show'])->name('show');
        Route::put('/{user}/approve-selfie',                     [AdminWorkerController::class, 'approveSelfie'])->name('approveSelfie');
        Route::put('/{user}/reject-selfie',                      [AdminWorkerController::class, 'rejectSelfie'])->name('rejectSelfie');
        Route::put('/{user}/override-badge',                     [AdminWorkerController::class, 'overrideBadge'])->name('overrideBadge');
        Route::put('/suspend-user/{user}',                       [AdminWorkerController::class, 'suspendUser'])->name('suspendUser');
        Route::put('/restore-user/{user}',                       [AdminWorkerController::class, 'restoreUser'])->name('restoreUser');
        Route::put('/{user}/documents/{document}/approve',       [AdminWorkerController::class, 'approveDocument'])->name('approveDocument');
        Route::put('/{user}/documents/{document}/reject',        [AdminWorkerController::class, 'rejectDocument'])->name('rejectDocument');
    });

    // Employer Management (Badge + Documents)
    Route::prefix('employers')->name('employers.')->group(function () {
        Route::get('/',                                          [AdminWorkerController::class, 'employerIndex'])->name('index');
        Route::get('/{user}',                                   [AdminWorkerController::class, 'employerShow'])->name('show');
        Route::put('/documents/{document}/approve',              [AdminWorkerController::class, 'approveEmployerDocument'])->name('approveDocument');
        Route::put('/documents/{document}/reject',               [AdminWorkerController::class, 'rejectEmployerDocument'])->name('rejectDocument');
        Route::put('/{user}/approve',                            [AdminWorkerController::class, 'approveEmployer'])->name('approve');
        Route::put('/{user}/reject',                             [AdminWorkerController::class, 'rejectEmployer'])->name('reject');
        Route::put('/{user}/suspend',                            [AdminWorkerController::class, 'suspendEmployer'])->name('suspend');
    });

    // Jobs
    Route::get('/jobs', [AdminJobController::class, 'index'])->name('jobs.index');
    Route::get('/jobs/{job}', [AdminJobController::class, 'show'])->name('jobs.show');
    Route::put('/jobs/{job}/status', [AdminJobController::class, 'updateStatus'])->name('jobs.updateStatus');
    Route::delete('/jobs/{job}', [AdminJobController::class, 'destroy'])->name('jobs.destroy');
    Route::post('/jobs/{job}/rescreen', [AdminJobController::class, 'rescreen'])->name('jobs.rescreen');
    Route::post('/jobs/bulk-screen', [AdminJobController::class, 'bulkScreen'])->name('jobs.bulkScreen');

    // Applications
    Route::get('/applications', [AdminApplicationController::class, 'index'])->name('applications.index');
    Route::put('/applications/{application}/status', [AdminApplicationController::class, 'updateStatus'])->name('applications.updateStatus');

    // Master Data
    Route::resource('categories', AdminCategoryController::class);
        Route::resource('cities', AdminCityController::class);
        Route::resource('skills', AdminSkillController::class);
        Route::resource('industries', AdminIndustryController::class);
        Route::resource('languages', AdminLanguageController::class);
        Route::resource('nationalities', AdminNationalityController::class);

        // Reports (M11)
        Route::get('/reports', [\App\Http\Controllers\Admin\AdminReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/{report}', [\App\Http\Controllers\Admin\AdminReportController::class, 'show'])->name('reports.show');
        Route::patch('/reports/{report}/status', [\App\Http\Controllers\Admin\AdminReportController::class, 'updateStatus'])->name('reports.update_status');
        Route::post('/reports/{user}/suspend-user', [\App\Http\Controllers\Admin\AdminReportController::class, 'suspendUser'])->name('reports.suspend_user');
        Route::post('/reports/{job}/suspend-job', [\App\Http\Controllers\Admin\AdminReportController::class, 'suspendJob'])->name('reports.suspend_job');

        // Payments & Subscriptions (M13)
        Route::get('/payments', [\App\Http\Controllers\Admin\AdminPaymentController::class, 'index'])->name('payments.index');
        Route::get('/subscriptions', [\App\Http\Controllers\Admin\AdminSubscriptionController::class, 'index'])->name('subscriptions.index');

        // Advertisements (M14)
        Route::get('/advertisements', [\App\Http\Controllers\Admin\AdminAdvertisementController::class, 'index'])->name('advertisements.index');
        Route::patch('/advertisements/{advertisement}/status', [\App\Http\Controllers\Admin\AdminAdvertisementController::class, 'updateStatus'])->name('advertisements.update_status');

        // Audit Logs (M17)
        Route::get('/audit-logs', [\App\Http\Controllers\Admin\AdminAuditLogController::class, 'index'])->name('audit_logs.index');
    });
});
