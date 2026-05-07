<x-mail::message>
# You bought these products!

Thank you for your purchase. Here is a summary of your order:

<x-mail::table>
| Product | Price |
| :--- | :--- |
@foreach($products as $product)
| **{{ $product['name'] }}**<br>@if(isset($product['path']) && file_exists($product['path']))<img src="{{ $message->embed($product['path']) }}" width="100" style="border-radius:8px;">@endif | {{ currency_symbol() }}{{ number_format($product['price'], 2) }} |
@endforeach
</x-mail::table>

## Total Paid: {{ currency_symbol() }}{{ number_format($total, 2) }}

We are preparing your order for shipment. You will receive another email once your items have shipped.

Thanks,<br>
{{ $storeName }}
</x-mail::message>
