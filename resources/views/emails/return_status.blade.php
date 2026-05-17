<x-mail::message>
# Return Status Updated

Hello {{ $return->user->name }},

The status of your return request for **Order #{{ $return->order_id }}** has been updated by **{{ $storeName }}**.

<x-mail::panel>
**Current Status:** {{ $statusLabel }}
</x-mail::panel>

{{ $statusMessage }}

@if($return->pickup_image)
### Pickup Confirmation
![Pickup Image]({{ asset($return->pickup_image) }})
@endif

@if($return->store_image)
### Store Return Confirmation
![Store Image]({{ asset($return->store_image) }})
@endif

Thanks,<br>
{{ $storeName }}
</x-mail::message>
