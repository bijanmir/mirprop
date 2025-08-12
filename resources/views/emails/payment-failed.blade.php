@component('mail::message')
# Payment Failed

We couldn’t process your payment for **{{ $payment->lease->unit->property->name }} — Unit {{ $payment->lease->unit->label }}**.

**Amount:** ${{ number_format($payment->amount_cents/100, 2) }}  
**Reason:** {{ $payment->failure_reason ?? 'Unknown' }}

@component('mail::button', ['url' => config('app.url')])
Try Again
@endcomponent

If this keeps happening, contact support.
@endcomponent
