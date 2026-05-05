<x-mail::message>
# New Delivery Assignment

Hello **{{ $order->deliveryBoy->name ?? 'Delivery Partner' }}**,

You have been assigned a new order from **{{ $storeName }}**.

**Order ID:** #ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}  
**Customer Name:** {{ $order->user->name }}  
**Total Items:** {{ $order->total_items }}

Please login to your delivery dashboard to view the full details and process the pickup.

<x-mail::button :url="route('delivery.dashboard')">
View Dashboard
</x-mail::button>

Thanks,<br>
{{ $storeName }}
</x-mail::message>
