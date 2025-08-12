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
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TenantPortalController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\WebhookController;
use App\Models\Lease;
use App\Models\MaintenanceTicket;
use App\Models\Payment;
use App\Models\Property;
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

// Tenant portal (public with token authentication)
Route::prefix('t')->name('tenant.')->group(function () {
    Route::get('/{token}', [TenantPortalController::class, 'show'])->name('portal');
    Route::post('/{token}/pay', [TenantPortalController::class, 'pay'])->name('pay');
    Route::post('/{token}/maintenance', [TenantPortalController::class, 'createMaintenanceRequest'])->name('maintenance.create');
});

// Webhook endpoints (public but signed)
Route::post('/webhooks/stripe', [WebhookController::class, 'stripe'])
    ->middleware('webhook.stripe')
    ->name('webhooks.stripe');

// Authentication required but no organization required
Route::middleware(['auth', 'verified'])->group(function () {
    // Organization creation for new users
    Route::get('/orgs/create', [OrganizationController::class, 'create'])->name('orgs.create');
    Route::post('/orgs', [OrganizationController::class, 'store'])->name('orgs.store');
    
    // Demo routes (for testing/preview)
    Route::prefix('demo')->name('demo.')->group(function () {
        Route::get('/properties', function () {
            $properties = Property::withCount(['units'])
                ->with(['units' => fn($q) => $q->select('id','property_id','status')])
                ->latest()->paginate(10);

            $occupiedCounts = $properties->mapWithKeys(function ($p) {
                $occupied = $p->units->where('status','occupied')->count();
                return [$p->id => $occupied];
            });

            return view('demo.properties.index', compact('properties','occupiedCounts'));
        })->name('properties');

        Route::get('/leases', function () {
            $leases = Lease::with(['unit.property','primaryContact'])->latest()->paginate(10);
            return view('demo.leases.index', compact('leases'));
        })->name('leases');

        Route::get('/payments', function () {
            $payments = Payment::with(['lease.unit.property','contact'])->latest('posted_at')->paginate(10);
            return view('demo.payments.index', compact('payments'));
        })->name('payments');

        Route::get('/tickets', function () {
            $tickets = MaintenanceTicket::with(['unit.property'])->latest()->paginate(10);
            return view('demo.tickets.index', compact('tickets'));
        })->name('tickets');
    });
});

// Main application routes (requires organization)
Route::middleware(['auth', 'verified', 'ensure.organization'])->group(function () {
    
    // Dashboard and HTMX partials
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::prefix('dashboard/partials')->name('dashboard.')->group(function () {
        Route::get('/metrics', [DashboardController::class, 'metrics'])->name('metrics');
        Route::get('/occupancy', [DashboardController::class, 'occupancy'])->name('occupancy');
        Route::get('/recent', [DashboardController::class, 'recent'])->name('recent');
    });
    
    // Organization management
    Route::resource('organizations', OrganizationController::class)->only(['index', 'show', 'edit', 'update']);
    Route::post('/organizations/{organization}/switch', [OrganizationController::class, 'switch'])->name('organizations.switch');
    
    // Property & Unit Management
    Route::resource('properties', PropertyController::class);
    Route::resource('properties.units', UnitController::class)->shallow();
    Route::resource('units', UnitController::class);
    
    // Contact Management
    Route::resource('contacts', ContactController::class);
    Route::resource('vendors', VendorController::class);
    
    // Lease Management
    Route::resource('leases', LeaseController::class);
    Route::post('/leases/{lease}/documents', [LeaseController::class, 'uploadDocument'])->name('leases.documents.store');
    Route::post('/leases/{lease}/ai-summary', [LeaseController::class, 'generateAiSummary'])->name('leases.ai-summary');
    
    // Payment Management
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('index');
        Route::get('/{payment}', [PaymentController::class, 'show'])->name('show');
        Route::post('/checkout', [PaymentController::class, 'checkout'])->name('checkout');
    });
    
    // Maintenance Management
    Route::resource('maintenance-tickets', MaintenanceTicketController::class);
    Route::prefix('maintenance-tickets/{ticket}')->name('maintenance-tickets.')->group(function () {
        Route::post('/events', [MaintenanceTicketController::class, 'addEvent'])->name('events.store');
        Route::patch('/assign', [MaintenanceTicketController::class, 'assign'])->name('assign');
    });
    
    // Document Management
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/{document}', [DocumentController::class, 'show'])->name('show');
        Route::delete('/{document}', [DocumentController::class, 'destroy'])->name('destroy');
    });
    
    // Announcements
    Route::resource('announcements', AnnouncementController::class)->only(['index', 'create', 'store', 'show']);
    
    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/rent-roll', [ReportController::class, 'rentRoll'])->name('rent-roll');
        Route::get('/delinquency', [ReportController::class, 'delinquency'])->name('delinquency');
        Route::get('/owner-statement', [ReportController::class, 'ownerStatement'])->name('owner-statement');
    });
    
    // User Profile Management
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });
});

// Include Breeze authentication routes
require __DIR__.'/auth.php';