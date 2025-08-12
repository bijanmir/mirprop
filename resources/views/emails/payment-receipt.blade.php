@component('mail::message')
# Payment Receipt

**Property:** {{ $property }} — Unit {{ $unit }}  
**Amount:** ${{ $amount }}  
**Method:** {{ $paymentMethod }}  
**Date:** {{ $paymentDate }}  
**Reference:** {{ $referenceNumber }}

@component('mail::button', ['url' => config('app.url')])
View in Portal
@endcomponent

Thanks,  
{{ config('app.name') }}
@endcomponent
