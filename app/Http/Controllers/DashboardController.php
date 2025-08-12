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

        $orgId = $organization->id;

        $metrics = [
            'total_units' => \App\Models\Unit::where('organization_id', $orgId)->count(),
            'occupied_units' => \App\Models\Unit::where('organization_id', $orgId)->where('status', 'occupied')->count(),
            'vacant_units' => \App\Models\Unit::where('organization_id', $orgId)->where('status', 'available')->count(),
            'open_tickets' => \App\Models\MaintenanceTicket::where('organization_id', $orgId)
                ->whereIn('status', ['open', 'assigned', 'in_progress'])->count(),
            'emergency_tickets' => \App\Models\MaintenanceTicket::where('organization_id', $orgId)
                ->where('priority', 'emergency')
                ->whereIn('status', ['open', 'assigned', 'in_progress'])->count(),
        ];

        $metrics['occupancy_rate'] = $metrics['total_units'] > 0
            ? round(($metrics['occupied_units'] / $metrics['total_units']) * 100, 1)
            : 0;

        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $monthlyCharges = \App\Models\LeaseCharge::whereHas('lease', fn($q) => $q->where('organization_id', $orgId))
            ->where('type', 'rent')
            ->whereBetween('due_date', [$startOfMonth, $endOfMonth])
            ->sum('amount_cents');

        $monthlyPayments = \App\Models\Payment::where('organization_id', $orgId)
            ->where('status', 'succeeded')
            ->whereBetween(\DB::raw('COALESCE(posted_at, created_at)'), [$startOfMonth, $endOfMonth])
            ->sum('amount_cents');

        $metrics['rent_due'] = $monthlyCharges;
        $metrics['rent_collected'] = $monthlyPayments;
        $metrics['collection_rate'] = $monthlyCharges > 0 ? round(($monthlyPayments / $monthlyCharges) * 100, 1) : 0;

        $recentLeases = \App\Models\Lease::with(['unit.property', 'primaryContact'])
            ->where('organization_id', $orgId)
            ->latest()->limit(5)->get();

        $recentPayments = \App\Models\Payment::with(['lease.unit.property', 'contact'])
            ->where('organization_id', $orgId)
            ->where('status', 'succeeded')
            ->orderByDesc('posted_at')
            ->orderByDesc('created_at')
            ->limit(5)->get();

        $recentTickets = \App\Models\MaintenanceTicket::with(['property', 'unit'])
            ->where('organization_id', $orgId)
            ->latest()->limit(5)->get();

        $upcomingExpirations = \App\Models\Lease::with(['unit.property', 'primaryContact'])
            ->where('organization_id', $orgId)
            ->where('status', 'active')
            ->whereBetween('end_date', [now(), now()->addDays(60)])
            ->orderBy('end_date')->limit(10)->get();

        return view('dashboard.index', compact(
            'metrics',
            'recentLeases',
            'recentPayments',
            'recentTickets',
            'upcomingExpirations'
        ));
    }
}