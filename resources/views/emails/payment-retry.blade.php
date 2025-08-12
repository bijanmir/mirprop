@component('mail::message')
# Please Retry Your Payment

We initiated a retry for your recent payment.

**Amount:** ${{ number_format($payment->amount_cents/100, 2) }}  
**New Payment Intent:** {{ $paymentIntent->id }}

You’ll get another receipt email when it succeeds.
@endcomponent
