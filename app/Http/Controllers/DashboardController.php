<?php

namespace App\Http\Controllers;

use App\Models\Lease;
use App\Models\MaintenanceTicket;
use App\Models\Payment;
use App\Models\Unit;
use App\Models\Property;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $organization = auth()->user()->currentOrganization;

        if (!$organization) {
            return redirect()->route('orgs.create');
        }

        $orgId = $organization->id;

        $stats = [
            'properties' => Property::where('organization_id', $orgId)->count(),
            'units' => Unit::where('organization_id', $orgId)->count(),
            'occupied_units' => Unit::where('organization_id', $orgId)->where('status', 'occupied')->count(),
            'monthly_revenue' => Lease::where('organization_id', $orgId)
                ->where('status', 'active')
                ->sum('rent_amount_cents'),
        ];

        $stats['occupancy_rate'] = $stats['units'] > 0
            ? round(($stats['occupied_units'] / $stats['units']) * 100, 1)
            : 0;

        $recentPayments = Payment::where('organization_id', $orgId)
            ->with(['contact', 'lease.unit.property'])
            ->whereNotNull('posted_at')
            ->latest('posted_at')
            ->limit(5)
            ->get();

        $openTickets = MaintenanceTicket::where('organization_id', $orgId)
            ->whereIn('status', ['open', 'in_progress'])
            ->with(['property', 'unit'])
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', compact('stats', 'recentPayments', 'openTickets'));
    }

    public function metrics()
    {
        $organization = auth()->user()->currentOrganization;
        if (!$organization) {
            return response()->json(['error' => 'No organization'], 400);
        }

        $orgId = $organization->id;

        $metrics = [
            'total_units' => Unit::where('organization_id', $orgId)->count(),
            'occupied_units' => Unit::where('organization_id', $orgId)->where('status', 'occupied')->count(),
            'vacant_units' => Unit::where('organization_id', $orgId)->where('status', 'available')->count(),
            'open_tickets' => MaintenanceTicket::where('organization_id', $orgId)
                ->whereIn('status', ['open', 'assigned', 'in_progress'])->count(),
            'emergency_tickets' => MaintenanceTicket::where('organization_id', $orgId)
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
            ->sum('amount_cents') ?? 0;

        $monthlyPayments = Payment::where('organization_id', $orgId)
            ->where('status', 'succeeded')
            ->whereBetween(\DB::raw('COALESCE(posted_at, created_at)'), [$startOfMonth, $endOfMonth])
            ->sum('amount_cents');

        $metrics['rent_due'] = $monthlyCharges;
        $metrics['rent_collected'] = $monthlyPayments;
        $metrics['collection_rate'] = $monthlyCharges > 0 ? 
            round(($monthlyPayments / $monthlyCharges) * 100, 1) : 0;

        return response()->json($metrics);
    }

    public function occupancy()
    {
        $organization = auth()->user()->currentOrganization;
        if (!$organization) {
            return response()->json(['error' => 'No organization'], 400);
        }

        $properties = Property::where('organization_id', $organization->id)
            ->withCount(['units', 'units as occupied_units_count' => function ($query) {
                $query->where('status', 'occupied');
            }])
            ->get();

        return view('dashboard.partials.occupancy', compact('properties'));
    }

    public function recent()
    {
        $organization = auth()->user()->currentOrganization;
        if (!$organization) {
            return response()->json(['error' => 'No organization'], 400);
        }

        $orgId = $organization->id;

        $recentPayments = Payment::where('organization_id', $orgId)
            ->with(['contact', 'lease.unit.property'])
            ->whereNotNull('posted_at')
            ->latest('posted_at')
            ->limit(5)
            ->get();

        $openTickets = MaintenanceTicket::where('organization_id', $orgId)
            ->whereIn('status', ['open', 'in_progress'])
            ->with(['property', 'unit'])
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard.partials.recent', compact('recentPayments', 'openTickets'));
    }
}