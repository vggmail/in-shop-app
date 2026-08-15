<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Order;

class TableBookingController extends Controller
{
    public function index()
    {
        // Fetch active dine-in orders that have a table number
        $activeOrders = Order::where('order_type', 'Dine-in')
            ->whereNotNull('table_number')
            ->whereNotIn('status', ['Completed', 'Cancelled', 'Delivered'])
            ->get()
            ->keyBy('table_number');

        return view('tables.index', compact('activeOrders'));
    }
}
