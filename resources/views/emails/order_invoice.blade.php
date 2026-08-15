<x-mail::message>
# Order Invoice

Hello {{ $order->customer->name ?? 'Valued Customer' }},

Thank you for your purchase from **{{ $tenant->company_name ?? config('app.name') }}**. 
Your order is being processed for **{{ $order->order_type }}**.

**Order Number:** #{{ $order->order_number }}  
**Date:** {{ $order->created_at->format('d M Y, h:i A') }}  
**Payment Status:** {{ $order->payment_status }}  
**Payment Method:** {{ $order->payment_method }}

@if($order->order_type == 'Home Delivery' && $order->delivery_address)
**Delivery Address:**  
{{ $order->delivery_address }}
@endif

<x-mail::table>
| Item | Qty | Price | Total |
| :--- | :-- | :---- | :---- |
@foreach($order->items as $item)
| **{{ $item->item->name ?? 'Unknown' }}** @if($item->variant) ({{ $item->variant->name }}) @endif @if($item->extras->count() > 0) <br> <small>Extras: {{ $item->extras->map(fn($e) => $e->extra->name)->join(', ') }}</small> @endif | {{ $item->quantity }} | ₹{{ number_format($item->price, 2) }} | ₹{{ number_format($item->total, 2) }} |
@endforeach
</x-mail::table>

<div style="text-align: right;">
**Subtotal:** ₹{{ number_format($order->total_amount, 2) }}  
@if($order->discount_amount > 0)
**Discount:** -₹{{ number_format($order->discount_amount, 2) }}  
@endif
**Grand Total:** ₹{{ number_format($order->grand_total, 2) }}
</div>

@if($order->note)
**Note:** {{ $order->note }}
@endif

Thanks,<br>
{{ $tenant->company_name ?? config('app.name') }}
</x-mail::message>
