<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentCheckoutRequest;
use App\Models\Lease;
use App\Models\Payment;
use Illuminate\Http\Request;
use Stripe\StripeClient;

class PaymentController extends Controller
{
    protected $stripe;
    
    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }
    
    public function index(Request $request)
    {
        $this->authorize('viewAny', Payment::class);
        
        $query = Payment::with(['lease.unit.property', 'contact']);
        
        // Filter for tenants
        if (auth()->user()->hasOrganizationRole('tenant')) {
            $contact = auth()->user()->currentOrganization->contacts()
                ->where('email', auth()->user()->email)
                ->first();
            
            if ($contact) {
                $query->where('contact_id', $contact->id);
            }
        }
        
        $payments = $query
            ->when($request->search, function ($query, $search) {
                $query->whereHas('contact', fn($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('lease.unit.property', fn($q) => $q->where('name', 'like', "%{$search}%"));
            })
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->lease_id, fn($q, $id) => $q->where('lease_id', $id))
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        if ($request->header('HX-Request')) {
            return view('payments.partials.table', compact('payments'));
        }
        
        return view('payments.index', compact('payments'));
    }
    
    public function checkout(PaymentCheckoutRequest $request)
    {
        $lease = Lease::findOrFail($request->lease_id);
        
        // Verify user can pay for this lease
        if (auth()->user()->hasOrganizationRole('tenant')) {
            $contact = auth()->user()->currentOrganization->contacts()
                ->where('email', auth()->user()->email)
                ->first();
            
            if (!$contact || $contact->id !== $lease->primary_contact_id) {
                abort(403);
            }
        } else {
            $this->authorize('view', $lease);
        }
        
        $amountCents = $request->amount * 100;
        
        $paymentIntent = $this->stripe->paymentIntents->create([
            'amount' => $amountCents,
            'currency' => 'usd',
            'payment_method_types' => [$request->payment_method],
            'metadata' => [
                'lease_id' => $lease->id,
                'organization_id' => $lease->organization_id,
                'contact_id' => $lease->primary_contact_id,
            ],
        ]);
        
        // Create pending payment record
        Payment::create([
            'organization_id' => $lease->organization_id,
            'lease_id' => $lease->id,
            'contact_id' => $lease->primary_contact_id,
            'amount_cents' => $amountCents,
            'processor_id' => $paymentIntent->id,
            'status' => 'pending',
        ]);
        
        return response()->json([
            'client_secret' => $paymentIntent->client_secret,
            'payment_intent_id' => $paymentIntent->id,
        ]);
    }
    
    public function show(Payment $payment)
    {
        $this->authorize('view', $payment);
        
        $payment->load(['lease.unit.property', 'contact', 'allocations.leaseCharge']);
        
        return view('payments.show', compact('payment'));
    }
}