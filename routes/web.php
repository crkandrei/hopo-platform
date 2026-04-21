<?php

use App\Http\Controllers\Admin\SubscriptionPlanController;
use App\Http\Controllers\Admin\TvaRateController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BirthdayReservationActionController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\LocationBridgeController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SubscriptionExpiredController;
use App\Http\Controllers\WebController;
use Illuminate\Support\Facades\Route;
use Spatie\Health\Http\Controllers\HealthCheckJsonResultsController;

// Health check endpoint pentru monitoring extern
Route::get('/health-check-results', HealthCheckJsonResultsController::class);

// Contact form route - trebuie să fie accesibilă de oriunde (mutată înainte de rutele cu domeniu)
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

// Rute pentru app.hopo.ro - aplicația (dashboard și restul)
Route::domain('app.hopo.ro')->group(function () {
    Route::get('/', function () {
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->isStaff()) {
                return redirect(($user->location && !$user->location->bracelet_required) ? '/start-session' : '/scan');
            }
            return redirect('/dashboard');
        }
        return redirect('/login');
    });
});

// Landing page routes - pentru www.hopo.ro sau hopo.ro (fără subdomain)
Route::domain('www.hopo.ro')->group(function () {
    Route::get('/', [WebController::class, 'index']);
    Route::post('/contact', [ContactController::class, 'store']);
    Route::get('/functionalitati', [WebController::class, 'functionalitati']);
    Route::get('/preturi', [WebController::class, 'preturi']);
    Route::get('/despre', [WebController::class, 'despre']);
    Route::get('/contact', [WebController::class, 'contact']);
    Route::get('/blog', fn() => view('blog.index'));
    Route::get('/blog/{slug}', function ($slug) {
        $allowed = [
            'cum-sa-deschizi-loc-de-joaca-pentru-copii-romania',
            'bon-fiscal-automat-loc-de-joaca',
            'rezervari-online-zile-nastere-loc-de-joaca',
            'bratari-barcode-rfid-loc-de-joaca',
        ];
        if (!in_array($slug, $allowed)) abort(404);
        return view('blog.articles.' . $slug);
    });
});

Route::domain('hopo.ro')->group(function () {
    Route::get('/', [WebController::class, 'index']);
    Route::post('/contact', [ContactController::class, 'store']);
    Route::get('/login', fn() => redirect('https://app.hopo.ro/login'));
    Route::get('/dashboard', fn() => redirect('https://app.hopo.ro/dashboard'));
    Route::get('/functionalitati', [WebController::class, 'functionalitati']);
    Route::get('/preturi', [WebController::class, 'preturi']);
    Route::get('/despre', [WebController::class, 'despre']);
    Route::get('/contact', [WebController::class, 'contact']);
    Route::get('/blog', fn() => view('blog.index'));
    Route::get('/blog/{slug}', function ($slug) {
        $allowed = [
            'cum-sa-deschizi-loc-de-joaca-pentru-copii-romania',
            'bon-fiscal-automat-loc-de-joaca',
            'rezervari-online-zile-nastere-loc-de-joaca',
            'bratari-barcode-rfid-loc-de-joaca',
        ];
        if (!in_array($slug, $allowed)) abort(404);
        return view('blog.articles.' . $slug);
    });
});

// Landing page default (pentru local development sau când nu există subdomain)
Route::get('/', function () {
    $host = request()->getHost();
    $parts = explode('.', $host);
    $subdomain = count($parts) > 2 ? $parts[0] : null;
    
    // Dacă subdomain-ul este 'app' și nu suntem în local, redirecționează la dashboard
    if ($subdomain === 'app' && !in_array($host, ['localhost', '127.0.0.1']) && !str_contains($host, 'localhost') && !str_contains($host, '127.0.0.1')) {
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->isStaff()) {
                return redirect(($user->location && !$user->location->bracelet_required) ? '/start-session' : '/scan');
            }
            return redirect('/dashboard');
        }
        return redirect('/login');
    }
    
    // Altfel, arată landing page
    return app(WebController::class)->index();
});

// Landing subpages (local development fallback)
Route::get('/functionalitati', [WebController::class, 'functionalitati']);
Route::get('/preturi', [WebController::class, 'preturi']);
Route::get('/despre', [WebController::class, 'despre']);
Route::get('/contact', [WebController::class, 'contact']);
Route::get('/blog', fn() => view('blog.index'));
Route::get('/blog/{slug}', function ($slug) {
    $allowed = [
        'cum-sa-deschizi-loc-de-joaca-pentru-copii-romania',
        'bon-fiscal-automat-loc-de-joaca',
        'rezervari-online-zile-nastere-loc-de-joaca',
        'bratari-barcode-rfid-loc-de-joaca',
    ];
    if (!in_array($slug, $allowed)) abort(404);
    return view('blog.articles.' . $slug);
});

// Auth routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Public birthday reservation action (confirm/reject via email link)
Route::get('/rezervari/{token}/{action}', BirthdayReservationActionController::class)
    ->where('token', '[0-9a-f-]{36}')
    ->where('action', 'confirm|reject')
    ->middleware('throttle:20,1')
    ->name('birthday-reservations.action');

// Public booking (no auth) - location resolved by slug
Route::get('/booking/{location:slug}', [App\Http\Controllers\PublicBookingController::class, 'showForm'])->name('booking.show');
Route::post('/booking/{location:slug}', [App\Http\Controllers\PublicBookingController::class, 'submitForm']);
Route::get('/booking/{location:slug}/confirmare', [App\Http\Controllers\PublicBookingController::class, 'confirmation'])->name('booking.confirmation');
Route::get('/booking/{location:slug}/packages', [App\Http\Controllers\PublicBookingController::class, 'getAvailablePackages'])->name('booking.packages');
Route::get('/booking/{location:slug}/slots', [App\Http\Controllers\PublicBookingController::class, 'getAvailableSlots'])->name('booking.slots');
Route::get('/booking/{location:slug}/availability', [App\Http\Controllers\PublicBookingController::class, 'getAvailability'])->name('booking.availability');

// Pre-check-in public (no auth)
Route::get('/pre-checkin/{location:slug}', [App\Http\Controllers\PublicPreCheckinController::class, 'showIndex'])->name('pre-checkin.index');
Route::post('/pre-checkin/{location:slug}/new', [App\Http\Controllers\PublicPreCheckinController::class, 'submitNew'])->name('pre-checkin.submit-new');
Route::get('/pre-checkin/{location:slug}/qr/{token}', [App\Http\Controllers\PublicPreCheckinController::class, 'showQr'])->name('pre-checkin.qr');
Route::get('/pre-checkin/{location:slug}/check-phone', [App\Http\Controllers\PublicPreCheckinController::class, 'checkPhone'])->name('pre-checkin.check-phone')->middleware('throttle:30,1');
Route::post('/pre-checkin/{location:slug}/existing', [App\Http\Controllers\PublicPreCheckinController::class, 'submitExisting'])->name('pre-checkin.submit-existing')->middleware('throttle:10,60');
Route::post('/pre-checkin/{location:slug}/existing/token', [App\Http\Controllers\PublicPreCheckinController::class, 'generateExistingToken'])->name('pre-checkin.generate-token')->middleware('throttle:10,60');
Route::post('/pre-checkin/{location:slug}/lookup', [App\Http\Controllers\PublicPreCheckinController::class, 'lookupPhone'])->name('pre-checkin.lookup')->middleware('throttle:10,60');
Route::get('/pre-checkin/{location:slug}/existing', [App\Http\Controllers\PublicPreCheckinController::class, 'showExistingPage'])->name('pre-checkin.show-existing');
Route::post('/pre-checkin/{location:slug}/existing/child', [App\Http\Controllers\PublicPreCheckinController::class, 'addChildToExisting'])->name('pre-checkin.add-child');
Route::get('/pre-checkin/{location:slug}/rules', [App\Http\Controllers\PublicPreCheckinController::class, 'viewRules'])->name('pre-checkin.rules');

// Location context routes (for COMPANY_ADMIN)
Route::middleware('auth')->group(function () {
    Route::post('/location-context/set', [App\Http\Controllers\LocationContextController::class, 'setLocation'])->name('location-context.set');
    Route::get('/location-context/locations', [App\Http\Controllers\LocationContextController::class, 'getLocations'])->name('location-context.locations');
});

Route::middleware('auth')->get('/subscription/blocked', [SubscriptionExpiredController::class, 'show'])
    ->name('subscription.blocked');

// Protected routes
Route::middleware('auth')->group(function () {
    // Dashboard - doar pentru SUPER_ADMIN și COMPANY_ADMIN (verificarea se face în controller)
    Route::get('/dashboard', [WebController::class, 'dashboard'])->name('dashboard');
    
    // Reports submenu routes (SUPER_ADMIN și COMPANY_ADMIN)
    Route::get('/reports/traffic', [App\Http\Controllers\ReportsController::class, 'traffic'])->name('reports.traffic');
    Route::get('/reports/general', [App\Http\Controllers\GeneralReportController::class, 'index'])->name('reports.general');
    Route::get('/reports/general/data', [App\Http\Controllers\GeneralReportController::class, 'data'])->name('reports.general.data');
    Route::get('/reports/gdpr-compliance', [App\Http\Controllers\ReportsController::class, 'gdprCompliance'])->name('reports.gdpr-compliance');
    Route::get('/reports/gdpr-compliance/data', [App\Http\Controllers\ReportsController::class, 'gdprComplianceData'])->name('reports.gdpr-compliance.data');
    Route::get('/reports/gdpr-compliance/pdf', [App\Http\Controllers\ReportsController::class, 'gdprCompliancePdf'])->name('reports.gdpr-compliance.pdf');
    Route::get('/reports/children', [App\Http\Controllers\SuperAdminReportsController::class, 'index'])->name('reports.children');
    Route::get('/reports/children/data', [App\Http\Controllers\SuperAdminReportsController::class, 'data'])->name('reports.children.data');
    // Redirect old route for backwards compatibility
    Route::get('/rapoarte', function() { return redirect()->route('reports.traffic'); })->name('reports.index');
    
    Route::get('/change-password', [AuthController::class, 'showChangePasswordForm'])->name('change-password');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('throttle:10,1');
    
    // Scan / Start session page (unified, mode determined by location config)
    Route::get('/scan', [App\Http\Controllers\ScanPageController::class, 'index'])->name('scan');
    Route::get('/start-session', [App\Http\Controllers\ScanPageController::class, 'index'])->name('start-session');

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
    Route::post('/sessions/{id}/mark-paid-no-fiscal', [App\Http\Controllers\SessionsController::class, 'markPaidNoFiscal'])->name('sessions.mark-paid-no-fiscal');
    Route::post('/sessions/mark-combined-paid-no-fiscal', [App\Http\Controllers\SessionsController::class, 'markCombinedPaidNoFiscal'])->name('sessions.mark-combined-paid-no-fiscal');
    Route::post('/sessions/mark-combined-paid-with-voucher', [App\Http\Controllers\SessionsController::class, 'markCombinedPaidWithVoucher'])->name('sessions.mark-combined-paid-with-voucher');
    Route::post('/sessions/{id}/toggle-payment-status', [App\Http\Controllers\SessionsController::class, 'togglePaymentStatus'])->name('sessions.toggle-payment-status');
    Route::post('/sessions/{id}/restart', [App\Http\Controllers\SessionsController::class, 'restartSession'])->name('sessions.restart');
    Route::post('/sessions/{id}/mark-free', [App\Http\Controllers\SessionsController::class, 'markFree'])->name('sessions.mark-free');
    Route::post('/sessions/{id}/toggle-session-type', [App\Http\Controllers\SessionsController::class, 'toggleSessionType'])->name('sessions.toggle-session-type');

    // Standalone receipts (Bon Specific)
    Route::get('/standalone-receipts/create', [App\Http\Controllers\StandaloneReceiptController::class, 'create'])->name('standalone-receipts.create');
    Route::post('/standalone-receipts', [App\Http\Controllers\StandaloneReceiptController::class, 'store'])->name('standalone-receipts.store');
    Route::get('/standalone-receipts/{standaloneReceipt}/pay', [App\Http\Controllers\StandaloneReceiptController::class, 'pay'])->name('standalone-receipts.pay');
    Route::post('/standalone-receipts/{standaloneReceipt}/prepare-fiscal-print', [App\Http\Controllers\StandaloneReceiptController::class, 'prepareFiscalPrint'])->name('standalone-receipts.prepare-fiscal-print');
    Route::post('/standalone-receipts/save-fiscal-receipt-log', [App\Http\Controllers\StandaloneReceiptController::class, 'saveFiscalReceiptLog'])->name('standalone-receipts.save-fiscal-receipt-log');
    Route::post('/standalone-receipts/{standaloneReceipt}/mark-paid-no-fiscal', [App\Http\Controllers\StandaloneReceiptController::class, 'markPaidNoFiscal'])->name('standalone-receipts.mark-paid-no-fiscal');

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
        Route::get('/pre-checkin/{token}', [App\Http\Controllers\ScanPageController::class, 'lookupPreCheckinToken']);
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
    
    // Onboarding wizard (super admin only)
    Route::get('/onboarding', [App\Http\Controllers\OnboardingController::class, 'create'])->name('onboarding.create');
    Route::post('/onboarding', [App\Http\Controllers\OnboardingController::class, 'store'])->name('onboarding.store');

    // Companies management (super admin only)
    Route::resource('companies', App\Http\Controllers\CompanyController::class);
    Route::delete('/companies/{company}/logo', [App\Http\Controllers\CompanyController::class, 'deleteLogo'])
        ->name('companies.logo.delete');

    // Locations management (super admin and company admin)
    Route::resource('locations', App\Http\Controllers\LocationController::class);

    // Location bridge management (super admin and company admin)
    Route::post('/locations/{location}/bridge/generate-key', [LocationBridgeController::class, 'generateKey'])
        ->name('locations.bridge.generate-key');
    Route::post('/locations/{location}/bridge/commands', [LocationBridgeController::class, 'createCommand'])
        ->name('locations.bridge.commands');

    // Subscriptions management (super admin only)
    Route::get('/admin/subscriptions', [SubscriptionController::class, 'index'])->name('admin.subscriptions.index');
    Route::get('/admin/subscriptions/{location}/create', [SubscriptionController::class, 'create'])->name('admin.subscriptions.create');
    Route::post('/admin/subscriptions/{location}', [SubscriptionController::class, 'store'])->name('admin.subscriptions.store');
    Route::get('/admin/subscriptions/{location}/history', [SubscriptionController::class, 'history'])->name('admin.subscriptions.history');
    Route::get('/admin/subscriptions/{subscription}/edit', [SubscriptionController::class, 'edit'])->name('admin.subscriptions.edit');
    Route::put('/admin/subscriptions/{subscription}', [SubscriptionController::class, 'update'])->name('admin.subscriptions.update');
    Route::post('/admin/subscriptions/{subscription}/suspend', [SubscriptionController::class, 'suspend'])->name('admin.subscriptions.suspend');

    // Vouchers (per location)
    Route::get('/locations/{location}/vouchers/report', [App\Http\Controllers\VoucherController::class, 'report'])->name('locations.vouchers.report');
    Route::resource('locations.vouchers', App\Http\Controllers\VoucherController::class);
    Route::post('/vouchers/validate', [App\Http\Controllers\VoucherController::class, 'validate'])->name('vouchers.validate');

    // Birthday halls, time slots, packages (per location)
    Route::resource('locations.birthday-halls', App\Http\Controllers\BirthdayHallController::class);
    Route::resource('birthday-halls.time-slots', App\Http\Controllers\BirthdayTimeSlotsController::class)->except(['create', 'show']);
    Route::resource('locations.birthday-packages', App\Http\Controllers\BirthdayPackageController::class);
    Route::resource('locations.packages', App\Http\Controllers\PackageController::class);
    Route::get('birthday-reservations/dashboard', [App\Http\Controllers\BirthdayReservationController::class, 'dashboard'])->name('birthday-reservations.dashboard');
    Route::resource('birthday-reservations', App\Http\Controllers\BirthdayReservationController::class)->except(['create', 'store']);
    
    // Users management (super admin and company admin)
    Route::resource('users', App\Http\Controllers\UserController::class);

    // Impersonation (super admin only) — stop must be before {user} to avoid route conflict
    Route::post('/impersonate/stop', [App\Http\Controllers\ImpersonationController::class, 'stopImpersonating'])->name('impersonate.stop');
    Route::post('/impersonate/{user}', [App\Http\Controllers\ImpersonationController::class, 'impersonate'])->name('impersonate.start');

    // Pricing management (super admin and company admin)
    Route::get('/pricing', [App\Http\Controllers\PricingController::class, 'index'])->name('pricing.index');
    Route::post('/pricing/mode', [App\Http\Controllers\PricingController::class, 'updatePricingMode'])->name('pricing.mode.update');
    Route::post('/pricing/tiered-rates', [App\Http\Controllers\PricingController::class, 'updateTieredRates'])->name('pricing.tiered-rates.update');
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

    // Monitoring dashboard (super admin only)
    Route::get('/admin/monitoring', [App\Http\Controllers\Admin\MonitoringController::class, 'index'])->name('admin.monitoring.index');
    
    // Superadmin reports (super admin only)
    Route::get('/superadmin-reports', [App\Http\Controllers\SuperAdminReportsController::class, 'index'])->name('superadmin-reports.index');
    Route::get('/superadmin-reports/data', [App\Http\Controllers\SuperAdminReportsController::class, 'data'])->name('superadmin-reports.data');
});

// Stripe Webhook (public, no auth, no CSRF — verified via Stripe signature)
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])->name('stripe.webhook');

// Checkout (company admin)
Route::middleware('auth')->group(function () {
    Route::get('/checkout/plans', [CheckoutController::class, 'plans'])->name('checkout.plans');
    Route::post('/checkout/session', [CheckoutController::class, 'createSession'])->name('checkout.session');
    Route::get('/payment/success', [CheckoutController::class, 'success'])->name('payment.success');
});

// Admin subscription plan management (super admin)
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::resource('subscription-plans', SubscriptionPlanController::class);
    Route::resource('tva-rates', TvaRateController::class);
});

// Legal documents accessible without authentication
Route::get('/legal/terms', [App\Http\Controllers\LegalController::class, 'termsPublic'])->name('legal.terms.public');
Route::get('/legal/gdpr', [App\Http\Controllers\LegalController::class, 'gdprPublic'])->name('legal.gdpr.public');
