<x-mail::message>
# Order Status Update

Hello {{ $order->customer->name ?? 'Valued Customer' }},

We wanted to let you know that the status of your order **#{{ $order->order_number }}** has changed.

**New Status:** {{ $order->status }}

@if($order->status == 'Preparing')
Your order has been received and we are now preparing it with fresh ingredients. 
@elseif($order->status == 'On the way')
Great news! Your delicious meal is out for delivery.
@elseif($order->status == 'Completed')
Your order has been successfully delivered. We hope you enjoy it!
@endif

**Order Summary:**
@foreach($order->items as $item)
- {{ $item->quantity }}x {{ $item->item->name ?? 'Item' }}
@endforeach

<x-mail::button :url="route('home')">
Visit Our Store
</x-mail::button>

Thanks,<br>
{{ $tenant->name ?? config('app.name') }}
</x-mail::message>
