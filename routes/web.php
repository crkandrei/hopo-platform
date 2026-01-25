<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\WebController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WebController::class, 'index']);

// Auth routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Location context routes (for COMPANY_ADMIN)
Route::middleware('auth')->group(function () {
    Route::post('/location-context/set', [App\Http\Controllers\LocationContextController::class, 'setLocation'])->name('location-context.set');
    Route::get('/location-context/locations', [App\Http\Controllers\LocationContextController::class, 'getLocations'])->name('location-context.locations');
});

// Protected routes
Route::middleware('auth')->group(function () {
    // Dashboard - doar pentru SUPER_ADMIN și COMPANY_ADMIN (verificarea se face în controller)
    Route::get('/dashboard', [WebController::class, 'dashboard'])->name('dashboard');
    
    // Reports submenu routes (SUPER_ADMIN și COMPANY_ADMIN)
    Route::get('/reports/traffic', [App\Http\Controllers\ReportsController::class, 'traffic'])->name('reports.traffic');
    Route::get('/reports/general', [App\Http\Controllers\GeneralReportController::class, 'index'])->name('reports.general');
    Route::get('/reports/general/data', [App\Http\Controllers\GeneralReportController::class, 'data'])->name('reports.general.data');
    Route::get('/reports/children', [App\Http\Controllers\SuperAdminReportsController::class, 'index'])->name('reports.children');
    Route::get('/reports/children/data', [App\Http\Controllers\SuperAdminReportsController::class, 'data'])->name('reports.children.data');
    // Redirect old route for backwards compatibility
    Route::get('/rapoarte', function() { return redirect()->route('reports.traffic'); })->name('reports.index');
    
    Route::get('/change-password', [AuthController::class, 'showChangePasswordForm'])->name('change-password');
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    
    // Scan page
    Route::get('/scan', [App\Http\Controllers\ScanPageController::class, 'index'])->name('scan');

    // End of day page (accessible to all authenticated users)
    Route::get('/end-of-day', [App\Http\Controllers\EndOfDayController::class, 'index'])->name('end-of-day.index');
    Route::get('/end-of-day/print-non-fiscal', [App\Http\Controllers\EndOfDayController::class, 'printNonFiscalReport'])->name('end-of-day.print-non-fiscal');
    Route::post('/end-of-day/print-z', [App\Http\Controllers\EndOfDayController::class, 'printZReport'])->name('end-of-day.print-z');
    Route::post('/end-of-day/save-z-report-log', [App\Http\Controllers\EndOfDayController::class, 'saveZReportLog'])->name('end-of-day.save-z-report-log');

    // Sessions page (read-only)
    Route::get('/sessions', [App\Http\Controllers\SessionsController::class, 'index'])->name('sessions.index');
    Route::get('/sessions/data', [App\Http\Controllers\SessionsController::class, 'data'])->name('sessions.data');
    Route::get('/sessions/{id}/show', [App\Http\Controllers\SessionsController::class, 'show'])->name('sessions.show');
    Route::get('/sessions/{id}/receipt', [App\Http\Controllers\SessionsController::class, 'receipt'])->name('sessions.receipt');
    Route::post('/sessions/{id}/prepare-fiscal-print', [App\Http\Controllers\SessionsController::class, 'prepareFiscalPrint'])->name('sessions.prepare-fiscal-print');
    Route::post('/sessions/prepare-combined-fiscal-print', [App\Http\Controllers\SessionsController::class, 'prepareCombinedFiscalPrint'])->name('sessions.prepare-combined-fiscal-print');
    Route::post('/sessions/save-fiscal-receipt-log', [App\Http\Controllers\SessionsController::class, 'saveFiscalReceiptLog'])->name('sessions.save-fiscal-receipt-log');
    Route::post('/sessions/save-combined-fiscal-receipt-log', [App\Http\Controllers\SessionsController::class, 'saveCombinedFiscalReceiptLog'])->name('sessions.save-combined-fiscal-receipt-log');
    Route::post('/sessions/{id}/mark-paid-with-voucher', [App\Http\Controllers\SessionsController::class, 'markPaidWithVoucher'])->name('sessions.mark-paid-with-voucher');
    Route::post('/sessions/{id}/toggle-payment-status', [App\Http\Controllers\SessionsController::class, 'togglePaymentStatus'])->name('sessions.toggle-payment-status');
    Route::post('/sessions/{id}/restart', [App\Http\Controllers\SessionsController::class, 'restartSession'])->name('sessions.restart');

    // Dashboard API (session-auth via web guard)
    Route::prefix('dashboard-api')->group(function () {
        Route::get('/stats', [App\Http\Controllers\DashboardApiController::class, 'stats']);
        Route::get('/alerts', [App\Http\Controllers\DashboardApiController::class, 'alerts']);
        Route::get('/active-sessions', [App\Http\Controllers\ScanPageController::class, 'getActiveSessions']);
        Route::post('/sessions/{id}/stop', [App\Http\Controllers\ScanPageController::class, 'stopSession']);
        Route::post('/sessions/{id}/pause', [App\Http\Controllers\ScanPageController::class, 'pauseSession']);
        Route::post('/sessions/{id}/resume', [App\Http\Controllers\ScanPageController::class, 'resumeSession']);
    });

        // Reports API (moved from dashboard)
        Route::prefix('reports-api')->group(function () {
            Route::get('/activity', [App\Http\Controllers\DashboardApiController::class, 'recentActivity']);
            Route::get('/reports', [App\Http\Controllers\DashboardApiController::class, 'reports']);
            Route::get('/entries', [App\Http\Controllers\DashboardApiController::class, 'entriesReport']);
        });

    // Scan API (session-auth via web guard)
    Route::prefix('scan-api')->group(function () {
        Route::post('/generate', [App\Http\Controllers\ScanPageController::class, 'generateCode']);
        Route::post('/lookup', [App\Http\Controllers\ScanPageController::class, 'lookupBracelet']);
        Route::post('/assign', [App\Http\Controllers\ScanPageController::class, 'assignBracelet']);
        Route::post('/create-child', [App\Http\Controllers\ScanPageController::class, 'createChild']);
        Route::post('/start-session', [App\Http\Controllers\ScanPageController::class, 'startSession']);
        Route::post('/stop-session/{id}', [App\Http\Controllers\ScanPageController::class, 'stopSession']);
        Route::post('/pause-session/{id}', [App\Http\Controllers\ScanPageController::class, 'pauseSession']);
        Route::post('/resume-session/{id}', [App\Http\Controllers\ScanPageController::class, 'resumeSession']);
        Route::post('/add-products', [App\Http\Controllers\ScanPageController::class, 'addProductsToSession']);
        Route::get('/active-sessions', [App\Http\Controllers\ScanPageController::class, 'getActiveSessions']);
        Route::get('/session-stats', [App\Http\Controllers\ScanPageController::class, 'getSessionStats']);
        Route::get('/recent-completed', [App\Http\Controllers\ScanPageController::class, 'recentCompletedSessions']);
        Route::get('/children-with-sessions', [App\Http\Controllers\ScanPageController::class, 'searchChildrenWithActiveSessions']);
        Route::get('/child-session/{childId}', [App\Http\Controllers\ScanPageController::class, 'lookupChildSession']);
        Route::post('/check-guardian-terms', [App\Http\Controllers\ScanPageController::class, 'checkGuardianTerms']);
        Route::post('/accept-guardian-terms', [App\Http\Controllers\ScanPageController::class, 'acceptGuardianTerms']);
        Route::post('/add-products', [App\Http\Controllers\ScanPageController::class, 'addProductsToSession']);
        Route::get('/available-products', [App\Http\Controllers\ScanPageController::class, 'getAvailableProducts']);
        Route::get('/session-products/{sessionId}', [App\Http\Controllers\ScanPageController::class, 'getSessionProducts']);
    });
    
    // Children management
    Route::get('/children/data', [App\Http\Controllers\ChildController::class, 'data'])->name('children.data');
    Route::get('/children-search', [App\Http\Controllers\ChildController::class, 'search'])->name('children.search');
    Route::resource('children', App\Http\Controllers\ChildController::class)
        ->where(['child' => '[0-9]+']);
    
    // Guardians management
    // STAFF poate vedea doar view-ul (show), nu CRUD complet (verificarea se face în controller)
    Route::get('/guardians/{guardian}', [App\Http\Controllers\GuardianController::class, 'show'])->name('guardians.show');
    Route::get('/guardians-search', [App\Http\Controllers\GuardianController::class, 'search'])->name('guardians.search');
    Route::get('/guardians', [App\Http\Controllers\GuardianController::class, 'index'])->name('guardians.index');
    Route::get('/guardians/create', [App\Http\Controllers\GuardianController::class, 'create'])->name('guardians.create');
    Route::post('/guardians', [App\Http\Controllers\GuardianController::class, 'store'])->name('guardians.store');
    Route::get('/guardians/{guardian}/edit', [App\Http\Controllers\GuardianController::class, 'edit'])->name('guardians.edit');
    Route::put('/guardians/{guardian}', [App\Http\Controllers\GuardianController::class, 'update'])->name('guardians.update');
    Route::delete('/guardians/{guardian}', [App\Http\Controllers\GuardianController::class, 'destroy'])->name('guardians.destroy');
    Route::get('/guardians-data', [App\Http\Controllers\GuardianController::class, 'data'])->name('guardians.data');
    
    // Products management - doar pentru SUPER_ADMIN și COMPANY_ADMIN (verificarea se face în controller)
    Route::resource('products', App\Http\Controllers\ProductController::class);
    
    // Legal documents (accessible without auth for parents to read)
    Route::get('/legal/terms', [App\Http\Controllers\LegalController::class, 'terms'])->name('legal.terms');
    Route::get('/legal/gdpr', [App\Http\Controllers\LegalController::class, 'gdpr'])->name('legal.gdpr');
    
    // Companies management (super admin only)
    Route::resource('companies', App\Http\Controllers\CompanyController::class);
    
    // Locations management (super admin and company admin)
    Route::resource('locations', App\Http\Controllers\LocationController::class);
    
    // Users management (super admin and company admin)
    Route::resource('users', App\Http\Controllers\UserController::class);
    
    // Pricing management (super admin and company admin)
    Route::get('/pricing', [App\Http\Controllers\PricingController::class, 'index'])->name('pricing.index');
    Route::get('/pricing/weekly-rates', [App\Http\Controllers\PricingController::class, 'showWeeklyRates'])->name('pricing.weekly-rates');
    Route::post('/pricing/weekly-rates', [App\Http\Controllers\PricingController::class, 'updateWeeklyRates'])->name('pricing.weekly-rates.update');
    Route::get('/pricing/special-periods', [App\Http\Controllers\PricingController::class, 'indexSpecialPeriods'])->name('pricing.special-periods');
    Route::post('/pricing/special-periods', [App\Http\Controllers\PricingController::class, 'storeSpecialPeriod'])->name('pricing.special-periods.store');
    Route::put('/pricing/special-periods/{id}', [App\Http\Controllers\PricingController::class, 'updateSpecialPeriod'])->name('pricing.special-periods.update');
    Route::delete('/pricing/special-periods/{id}', [App\Http\Controllers\PricingController::class, 'destroySpecialPeriod'])->name('pricing.special-periods.destroy');
    
    // Fiscal receipts (super admin only)
    Route::get('/fiscal-receipts', [App\Http\Controllers\FiscalReceiptController::class, 'index'])->name('fiscal-receipts.index');
    Route::post('/fiscal-receipts/calculate-price', [App\Http\Controllers\FiscalReceiptController::class, 'calculatePrice'])->name('fiscal-receipts.calculate-price');
    Route::post('/fiscal-receipts/prepare-print', [App\Http\Controllers\FiscalReceiptController::class, 'preparePrint'])->name('fiscal-receipts.prepare-print');
    Route::post('/fiscal-receipts/prepare-print-one-leu', [App\Http\Controllers\FiscalReceiptController::class, 'preparePrintOneLeu'])->name('fiscal-receipts.prepare-print-one-leu');
    Route::post('/fiscal-receipts/handle-result', [App\Http\Controllers\FiscalReceiptController::class, 'handlePrintResult'])->name('fiscal-receipts.handle-result');
    
    // Fiscal receipt logs (super admin only)
    Route::get('/fiscal-receipt-logs', [App\Http\Controllers\FiscalReceiptLogController::class, 'index'])->name('fiscal-receipt-logs.index');
    Route::get('/fiscal-receipt-logs/data', [App\Http\Controllers\FiscalReceiptLogController::class, 'data'])->name('fiscal-receipt-logs.data');
    
    // Anomalies (super admin only)
    Route::get('/anomalies', [App\Http\Controllers\AnomaliesController::class, 'index'])->name('anomalies.index');
    Route::post('/anomalies/scan', [App\Http\Controllers\AnomaliesController::class, 'scan'])->name('anomalies.scan');
    Route::get('/anomalies/{type}/sessions', [App\Http\Controllers\AnomaliesController::class, 'getSessions'])->name('anomalies.sessions');
    
    // Superadmin reports (super admin only)
    Route::get('/superadmin-reports', [App\Http\Controllers\SuperAdminReportsController::class, 'index'])->name('superadmin-reports.index');
    Route::get('/superadmin-reports/data', [App\Http\Controllers\SuperAdminReportsController::class, 'data'])->name('superadmin-reports.data');
});

// Legal documents accessible without authentication
Route::get('/legal/terms', [App\Http\Controllers\LegalController::class, 'terms'])->name('legal.terms.public');
Route::get('/legal/gdpr', [App\Http\Controllers\LegalController::class, 'gdpr'])->name('legal.gdpr.public');
