<?php

namespace App\Http\Controllers;

use App\Models\Lease;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class TenantPortalController extends Controller
{
    public function show($token)
    {
        try {
            $leaseId = Crypt::decryptString($token);
            $lease = Lease::with(['unit.property', 'primaryContact', 'charges'])->findOrFail($leaseId);
            
            // Calculate balance
            $balance = $lease->charges()->sum('balance_cents');
            
            // Get recent payments
            $payments = $lease->payments()
                ->where('status', 'succeeded')
                ->latest()
                ->limit(5)
                ->get();
            
            // Get maintenance tickets
            $tickets = $lease->unit->maintenanceTickets()
                ->where('contact_id', $lease->primary_contact_id)
                ->latest()
                ->limit(5)
                ->get();
            
            return view('tenant-portal.show', compact('lease', 'balance', 'payments', 'tickets', 'token'));
            
        } catch (\Exception $e) {
            abort(404);
        }
    }
    
    public function pay(Request $request, $token)
    {
        try {
            $leaseId = Crypt::decryptString($token);
            $lease = Lease::findOrFail($leaseId);
            
            $request->validate([
                'amount' => ['required', 'numeric', 'min:1'],
                'payment_method' => ['required', 'in:ach,card'],
            ]);
            
            // Redirect to payment processing
            // This would integrate with Stripe or similar
            
            return response()->json([
                'message' => 'Payment processing initiated',
                'redirect' => route('tenant.payment.process', $token),
            ]);
            
        } catch (\Exception $e) {
            abort(404);
        }
    }
    
    public function createMaintenanceRequest(Request $request, $token)
    {
        try {
            $leaseId = Crypt::decryptString($token);
            $lease = Lease::findOrFail($leaseId);
            
            $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'description' => ['required', 'string', 'max:5000'],
                'priority' => ['required', 'in:low,medium,high,emergency'],
            ]);
            
            $ticket = $lease->unit->maintenanceTickets()->create([
                'organization_id' => $lease->organization_id,
                'property_id' => $lease->unit->property_id,
                'contact_id' => $lease->primary_contact_id,
                'title' => $request->title,
                'description' => $request->description,
                'priority' => $request->priority,
                'status' => 'open',
            ]);
            
            return redirect()
                ->route('tenant.portal', $token)
                ->with('success', 'Maintenance request submitted successfully');
                
        } catch (\Exception $e) {
            abort(404);
        }
    }
}