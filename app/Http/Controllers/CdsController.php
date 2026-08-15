<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class CdsController extends Controller
{
    // Statuses that should appear on the CDS
    // We show Preparing (so cashier knows what's coming) and Ready (priority for handover)
    private const CDS_STATUSES = ['Preparing', 'Ready'];

    public function index()
    {
        $orders = Order::with(['items.item', 'customer'])
            ->whereIn('status', self::CDS_STATUSES)
            ->where('status', '!=', 'Pending Payment')
            ->orderByRaw("FIELD(status, 'Ready', 'Preparing') ASC")
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin.cds.index', compact('orders'));
    }

    /**
     * AJAX: Update status or payment from CDS board.
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        if ($request->has('payment_status')) {
            $order->payment_status = $request->payment_status;
        }

        if ($request->has('status')) {
            $order->status = $request->status;
        }

        $order->save();

        return response()->json([
            'success' => true, 
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'payment_method' => $order->payment_method
        ]);
    }

    /**
     * AJAX: Poll for CDS updates.
     */
    public function poll()
    {
        $orders = Order::with(['items.item', 'customer'])
            ->whereIn('status', self::CDS_STATUSES)
            ->where('status', '!=', 'Pending Payment')
            ->orderByRaw("FIELD(status, 'Ready', 'Preparing') ASC")
            ->orderBy('created_at', 'asc')
            ->get();

        $readyCount = $orders->where('status', 'Ready')->count();

        $formatted = $orders->map(function (Order $o) {
            return [
                'id'            => $o->id,
                'order_number'  => $o->order_number,
                'token_number'  => $o->token_number,
                'order_type'    => $o->order_type,
                'table_number'  => $o->table_number,
                'status'        => $o->status,
                'payment_status'=> $o->payment_status,
                'payment_method'=> $o->payment_method,
                'grand_total'   => $o->grand_total,
                'customer'      => $o->customer?->name ?? 'Guest',
                'created_at_ts' => $o->created_at->timestamp,
                'items_count'   => $o->items->sum('quantity')
            ];
        });

        return response()->json([
            'orders' => $formatted,
            'ready_count' => $readyCount
        ]);
    }
}
