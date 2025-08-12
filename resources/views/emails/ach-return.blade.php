@component('mail::message')
# ACH Return Notice

Your ACH payment was **returned**.

**Amount:** ${{ number_format($payment->amount_cents/100, 2) }}  
**Return Code:** {{ $returnData['return_code'] ?? 'N/A' }}

Please update your payment method and try again.
@endcomponent
