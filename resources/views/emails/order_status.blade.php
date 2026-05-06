<x-mail::message>
# Order Status Updated

Hello **{{ $customerName }}**,

Your order **{{ $orderId }}** has been updated to:

<x-mail::panel>
<strong style="font-size: 18px; text-transform: uppercase;">{{ $newStatus }}</strong>
</x-mail::panel>

{{ $statusMessage }}

@if($pickupImage && $newStatus == 'Shipped')
### Pickup Confirmation
The pickup proof photo has been **attached to this email** for your security.
@endif

@if($deliveryImage && $newStatus == 'Delivered')
### Delivery Confirmation
The delivery proof photo has been **attached to this email** for your security.
@endif

<x-mail::table>
| Item | Qty | Price |
| :--- | :---: | :--- |
@if(is_array($order->items_json))
@foreach($order->items_json as $item)
| {{ $item['name'] }} | {{ $item['qty'] }} | ${{ number_format($item['price'] * $item['qty'], 2) }} |
@endforeach
@endif
</x-mail::table>

**Order Total: ${{ number_format($order->total_price, 2) }}**

If you have any questions about your order, feel free to reach out to our support team.

Thanks,<br>
{{ $storeName }}
</x-mail::message>
