<?php

namespace App\Http\Controllers;

use App\Models\Lease;
use App\Models\LeaseCharge;
use App\Models\Payment;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ReportController extends Controller
{
    use AuthorizesRequests;
    public function rentRoll(Request $request)
    {
        $this->authorize('viewAny', Lease::class);
        
        $month = $request->get('month', now()->format('Y-m'));
        $startDate = \Carbon\Carbon::parse($month)->startOfMonth();
        $endDate = \Carbon\Carbon::parse($month)->endOfMonth();
        
        $leases = Lease::with(['unit.property', 'primaryContact', 'charges', 'payments'])
            ->where('status', 'active')
            ->when($request->property_id, function ($query, $propertyId) {
                $query->whereHas('unit', fn($q) => $q->where('property_id', $propertyId));
            })
            ->get();
        
        $report = $leases->map(function ($lease) use ($startDate, $endDate) {
            $monthlyCharge = $lease->charges()
                ->where('type', 'rent')
                ->whereBetween('due_date', [$startDate, $endDate])
                ->first();
            
            $paid = $lease->payments()
                ->where('status', 'succeeded')
                ->whereBetween('posted_at', [$startDate, $endDate])
                ->sum('amount_cents');
            
            return [
                'lease' => $lease,
                'charge_amount' => $monthlyCharge?->amount_cents ?? 0,
                'paid_amount' => $paid,
                'balance' => ($monthlyCharge?->amount_cents ?? 0) - $paid,
            ];
        });
        
        $totals = [
            'charged' => $report->sum('charge_amount'),
            'collected' => $report->sum('paid_amount'),
            'outstanding' => $report->sum('balance'),
        ];
        
        $properties = Property::all();
        
        return view('reports.rent-roll', compact('report', 'totals', 'month', 'properties'));
    }
    
    public function delinquency(Request $request)
    {
        $this->authorize('viewAny', Payment::class);
        
        $daysOverdue = $request->get('days', 30);
        $cutoffDate = now()->subDays($daysOverdue);
        
        $delinquent = LeaseCharge::with(['lease.unit.property', 'lease.primaryContact'])
            ->where('balance_cents', '>', 0)
            ->where('due_date', '<', $cutoffDate)
            ->whereHas('lease', fn($q) => $q->where('status', 'active'))
            ->when($request->property_id, function ($query, $propertyId) {
                $query->whereHas('lease.unit', fn($q) => $q->where('property_id', $propertyId));
            })
            ->orderBy('due_date')
            ->get()
            ->groupBy('lease_id');
        
        $report = $delinquent->map(function ($charges, $leaseId) {
            $lease = $charges->first()->lease;
            return [
                'lease' => $lease,
                'total_owed' => $charges->sum('balance_cents'),
                'oldest_charge' => $charges->sortBy('due_date')->first(),
                'charge_count' => $charges->count(),
            ];
        });
        
        $totals = [
            'count' => $report->count(),
            'amount' => $report->sum('total_owed'),
        ];
        
        $properties = Property::all();
        
        return view('reports.delinquency', compact('report', 'totals', 'daysOverdue', 'properties'));
    }
    
    public function ownerStatement(Request $request)
    {
        $this->authorize('viewAny', Payment::class);
        
        $month = $request->get('month', now()->format('Y-m'));
        $propertyId = $request->get('property_id');
        
        if (!$propertyId) {
            return redirect()
                ->route('reports.owner-statement')
                ->with('error', 'Please select a property');
        }
        
        $property = Property::findOrFail($propertyId);
        $this->authorize('view', $property);
        
        $startDate = \Carbon\Carbon::parse($month)->startOfMonth();
        $endDate = \Carbon\Carbon::parse($month)->endOfMonth();
        
        // Income
        $payments = Payment::with(['lease.unit'])
            ->whereHas('lease.unit', fn($q) => $q->where('property_id', $propertyId))
            ->where('status', 'succeeded')
            ->whereBetween('posted_at', [$startDate, $endDate])
            ->get();
        
        // Expenses (from maintenance tickets)
        $expenses = DB::table('maintenance_events')
            ->join('maintenance_tickets', 'maintenance_events.ticket_id', '=', 'maintenance_tickets.id')
            ->where('maintenance_tickets.property_id', $propertyId)
            ->where('maintenance_events.type', 'cost')
            ->whereBetween('maintenance_events.created_at', [$startDate, $endDate])
            ->sum('maintenance_events.cost_cents');
        
        $income = $payments->sum('amount_cents');
        $netIncome = $income - $expenses;
        
        $properties = Property::all();
        
        return view('reports.owner-statement', compact(
            'property',
            'payments',
            'income',
            'expenses',
            'netIncome',
            'month',
            'properties'
        ));
    }
}