<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\LeaseController;
use App\Http\Controllers\MaintenanceTicketController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\TenantPortalController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/', function () {
    return view('welcome');
});

// Tenant portal (public with token)
Route::prefix('t')->name('tenant.')->group(function () {
    Route::get('/{token}', [TenantPortalController::class, 'show'])->name('portal');
    Route::post('/{token}/pay', [TenantPortalController::class, 'pay'])->name('pay');
    Route::post('/{token}/maintenance', [TenantPortalController::class, 'createMaintenanceRequest'])->name('maintenance.create');
});

// Webhook endpoints (public but signed)
Route::post('/webhooks/stripe', [WebhookController::class, 'stripe'])
    ->middleware('webhook.stripe')
    ->name('webhooks.stripe');

// Authenticated routes
Route::middleware(['auth', 'verified', 'ensure.organization'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Organization management
    Route::resource('organizations', OrganizationController::class)->only(['index', 'store', 'update']);
    Route::post('/organizations/{organization}/switch', [OrganizationController::class, 'switch'])->name('organizations.switch');
    
    // Properties and Units
    Route::resource('properties', PropertyController::class);
    Route::resource('properties.units', UnitController::class)->shallow();
    
    // Contacts and Vendors
    Route::resource('contacts', ContactController::class);
    Route::resource('vendors', VendorController::class);
    
    // Leases
    Route::resource('leases', LeaseController::class);
    Route::post('/leases/{lease}/documents', [LeaseController::class, 'uploadDocument'])->name('leases.documents.store');
    Route::post('/leases/{lease}/ai-summary', [LeaseController::class, 'generateAiSummary'])->name('leases.ai-summary');
    
    // Payments
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('/payments/checkout', [PaymentController::class, 'checkout'])->name('payments.checkout');
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    
    // Maintenance
    Route::resource('maintenance-tickets', MaintenanceTicketController::class);
    Route::post('/maintenance-tickets/{ticket}/events', [MaintenanceTicketController::class, 'addEvent'])->name('maintenance-tickets.events.store');
    Route::patch('/maintenance-tickets/{ticket}/assign', [MaintenanceTicketController::class, 'assign'])->name('maintenance-tickets.assign');
    
    // Documents
    Route::get('/documents/{document}', [DocumentController::class, 'show'])->name('documents.show');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    
    // Announcements
    Route::resource('announcements', AnnouncementController::class)->only(['index', 'create', 'store', 'show']);
    
    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/rent-roll', [ReportController::class, 'rentRoll'])->name('rent-roll');
        Route::get('/delinquency', [ReportController::class, 'delinquency'])->name('delinquency');
        Route::get('/owner-statement', [ReportController::class, 'ownerStatement'])->name('owner-statement');
    });
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Include Breeze authentication routes
require __DIR__.'/auth.php';