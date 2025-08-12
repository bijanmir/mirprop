<?php

namespace App\Http\Controllers;

use App\Models\Lease;
use App\Models\MaintenanceTicket;
use App\Models\Payment;
use App\Models\Unit;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $organization = auth()->user()->currentOrganization;
        
        if (!$organization) {
            return redirect()->route('organizations.create');
        }
        
        // Metrics
        $metrics = [
            'total_units' => Unit::count(),
            'occupied_units' => Unit::where('status', 'occupied')->count(),
            'vacant_units' => Unit::where('status', 'available')->count(),
            'open_tickets' => MaintenanceTicket::whereIn('status', ['open', 'assigned', 'in_progress'])->count(),
            'emergency_tickets' => MaintenanceTicket::where('priority', 'emergency')
                ->whereIn('status', ['open', 'assigned', 'in_progress'])
                ->count(),
        ];
        
        // Calculate occupancy rate
        $metrics['occupancy_rate'] = $metrics['total_units'] > 0 
            ? round(($metrics['occupied_units'] / $metrics['total_units']) * 100, 1)
            : 0;
        
        // Calculate collection rate for current month
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();
        
        $monthlyCharges = \App\Models\LeaseCharge::where('type', 'rent')
            ->whereBetween('due_date', [$startOfMonth, $endOfMonth])
            ->sum('amount_cents');
        
        $monthlyPayments = Payment::where('status', 'succeeded')
            ->whereBetween('posted_at', [$startOfMonth, $endOfMonth])
            ->sum('amount_cents');
        
        $metrics['rent_due'] = $monthlyCharges;
        $metrics['rent_collected'] = $monthlyPayments;
        $metrics['collection_rate'] = $monthlyCharges > 0 
            ? round(($monthlyPayments / $monthlyCharges) * 100, 1)
            : 0;
        
        // Recent data
        $recentLeases = Lease::with(['unit.property', 'primaryContact'])
            ->latest()
            ->limit(5)
            ->get();
        
        $recentPayments = Payment::with(['lease.unit', 'contact'])
            ->where('status', 'succeeded')
            ->latest('posted_at')
            ->limit(5)
            ->get();
        
        $recentTickets = MaintenanceTicket::with(['property', 'unit'])
            ->latest()
            ->limit(5)
            ->get();
        
        // Upcoming lease expirations (next 60 days)
        $upcomingExpirations = Lease::with(['unit.property', 'primaryContact'])
            ->where('status', 'active')
            ->whereBetween('end_date', [now(), now()->addDays(60)])
            ->orderBy('end_date')
            ->limit(10)
            ->get();
        
        return view('dashboard.index', compact(
            'metrics',
            'recentLeases',
            'recentPayments',
            'recentTickets',
            'upcomingExpirations'
        ));
    }
}