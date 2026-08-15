<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class KdsController extends Controller
{
    // Statuses that should appear on the KDS
    private const KDS_STATUSES = ['Preparing', 'Ready'];

    /**
     * Main KDS board view.
     */
    public function index()
    {
        $orders = Order::with(['items.item', 'items.variant', 'items.extras.extra', 'customer'])
            ->whereIn('status', self::KDS_STATUSES)
            ->where('status', '!=', 'Pending Payment')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin.kds.index', compact('orders'));
    }

    /**
     * AJAX: Update order status from KDS board.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Preparing,Ready,Completed'
        ]);

        $order = Order::findOrFail($id);
        $order->status = $request->status;
        $order->save();

        return response()->json(['success' => true, 'status' => $order->status]);
    }

    /**
     * AJAX: Poll for new/updated orders (for auto-refresh without full page reload).
     */
    public function poll()
    {
        $orders = Order::with(['items.item', 'items.variant', 'items.extras.extra', 'customer'])
            ->whereIn('status', self::KDS_STATUSES)
            ->where('status', '!=', 'Pending Payment')
            ->orderBy('created_at', 'asc')
            ->get();

        $preparingCount = $orders->where('status', 'Preparing')->count();
        $readyCount     = $orders->where('status', 'Ready')->count();

        $formatted = $orders->map(function (Order $o) {
            return [
                'id'            => $o->id,
                'order_number'  => $o->order_number,
                'token_number'  => $o->token_number,
                'order_type'    => $o->order_type,
                'table_number'  => $o->table_number,
                'source'        => $o->source,
                'note'          => $o->note,
                'status'        => $o->status,
                'created_at_ts' => $o->created_at->timestamp,
                'customer'      => $o->customer?->name,
                'items'         => $o->items->map(function ($item) {
                    return [
                        'quantity' => $item->quantity,
                        'name'     => $item->item?->name ?? 'Item #' . $item->item_id,
                        'variant'  => $item->variant?->name ?? null,
                        'extras'   => $item->extras->count() > 0
                            ? $item->extras->map(fn($e) => $e->extra?->name ?? 'Extra')->join(', ')
                            : null,
                    ];
                }),
            ];
        });

        return response()->json([
            'orders'          => $formatted,
            'preparing_count' => $preparingCount,
            'ready_count'     => $readyCount,
        ]);
    }
}
