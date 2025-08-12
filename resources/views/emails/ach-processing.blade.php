@component('mail::message')
# ACH Payment Processing

Your ACH payment is **processing**. This can take 3–5 business days.  
We’ll email you when it completes.

**Amount:** ${{ number_format($payment->amount_cents/100, 2) }}  
**Property:** {{ $payment->lease->unit->property->name }} — Unit {{ $payment->lease->unit->label }}
@endcomponent
