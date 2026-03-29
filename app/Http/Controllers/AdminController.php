<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller {
    public function dashboard(Request $request) {
        $range = $request->get('range', '30d');
        $days = ($range === '6m') ? 180 : 30;

        $todaySales = Order::whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])->sum('grand_total');
        $totalOrders = Order::count();
        $totalRevenue = Order::sum("grand_total");
        $totalCustomers = \App\Models\Customer::count();

        // Real Top Items by Sales Count
        $topItems = \App\Models\OrderItem::select('item_id', \DB::raw('count(*) as total'))
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('item_id')
            ->orderBy('total', 'desc')
            ->with('item')
            ->take(5)
            ->get();

        // Sales Trends based on filter
        $dailySales = Order::selectRaw("DATE(created_at) as date, SUM(grand_total) as total")
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy("date")
            ->orderBy("date", "asc")
            ->get();
            
        return view("admin.dashboard", compact("todaySales", "totalOrders", "totalRevenue", "totalCustomers", "topItems", "dailySales", "range"));
    }

    public function logs() {
        $logs = \App\Models\ActivityLog::with('user')->orderBy('id', 'desc')->paginate(50);
        return view("admin.logs.index", compact("logs"));
    }
}
