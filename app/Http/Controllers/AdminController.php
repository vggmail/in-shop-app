<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller {
    public function dashboard(Request $request) {
        $tab = $request->query('tab', 'analytics');

        if ($tab === 'analytics') {
            $range = $request->get('range', '30');
            $days = (int)$range;

            $startDate = now()->subDays($days)->startOfDay();
                
            // Core Metrics
            $grossSales = \App\Models\Order::whereNotIn('status', ['Pending Payment', 'Payment Failed'])
                ->where('created_at', '>=', $startDate)->sum('grand_total');
            $totalExpenses = \App\Models\Expense::where('date', '>=', $startDate)->sum('amount');
            $netProfit = $grossSales - $totalExpenses;
            $orderCount = \App\Models\Order::whereNotIn('status', ['Pending Payment', 'Payment Failed'])
                ->where('created_at', '>=', $startDate)->count();

            // Time-series for Line Chart
            $chartDates = [];
            $salesData = [];
            $expensesData = [];
            
            for ($i = $days - 1; $i >= 0; $i--) {
                $dateStr = now()->subDays($i)->format('Y-m-d');
                $chartDates[] = now()->subDays($i)->format('d M');
                
                $salesData[] = \App\Models\Order::whereNotIn('status', ['Pending Payment', 'Payment Failed'])
                    ->whereDate('created_at', $dateStr)->sum('grand_total');
                $expensesData[] = \App\Models\Expense::whereDate('date', $dateStr)->sum('amount');
            }

            // Top Items for Doughnut Chart
            $topItemsQuery = \Illuminate\Support\Facades\DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('items', 'order_items.item_id', '=', 'items.id')
                ->whereNotIn('orders.status', ['Pending Payment', 'Payment Failed'])
                ->where('orders.created_at', '>=', $startDate)
                ->select(\Illuminate\Support\Facades\DB::raw('items.name, sum(order_items.quantity) as total_qty'))
                ->groupBy('items.id', 'items.name')
                ->orderByDesc('total_qty')
                ->limit(5)
                ->get();
                
            $topItemLabels = $topItemsQuery->pluck('name')->toArray();
            $topItemData = $topItemsQuery->pluck('total_qty')->toArray();

            return view("admin.dashboard_analytics", compact(
                'range', 'grossSales', 'totalExpenses', 'netProfit', 'orderCount', 
                'chartDates', 'salesData', 'expensesData', 'topItemLabels', 'topItemData', 'tab'
            ));
        }

        // Classic Dashboard Logic
        $range = $request->get('range', '30d');
        $days = ($range === '6m') ? 180 : 30;

        $todaySales = Order::whereNotIn('status', ['Pending Payment', 'Payment Failed'])
            ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
            ->sum('grand_total');
        $totalOrders = Order::whereNotIn('status', ['Pending Payment', 'Payment Failed'])->count();
        $totalRevenue = Order::whereNotIn('status', ['Pending Payment', 'Payment Failed'])->sum("grand_total");
        $totalCustomers = \App\Models\Customer::count();

        // Real Top Items by Sales Count
        $topItems = \App\Models\OrderItem::whereHas('order', function($q) {
                $q->whereNotIn('status', ['Pending Payment', 'Payment Failed']);
            })
            ->select('item_id', \DB::raw('count(*) as total'))
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('item_id')
            ->orderBy('total', 'desc')
            ->with(['item' => function($q) {
                $q->withTrashed();
            }])
            ->take(5)
            ->get();

        // Sales Trends based on filter
        $dailySales = Order::whereNotIn('status', ['Pending Payment', 'Payment Failed'])
            ->selectRaw("DATE(created_at) as date, SUM(grand_total) as total")
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy("date")
            ->orderBy("date", "asc")
            ->get();
            
        return view("admin.dashboard", compact("todaySales", "totalOrders", "totalRevenue", "totalCustomers", "topItems", "dailySales", "range", "tab"));
    }

    public function logs() {
        $logs = \App\Models\ActivityLog::with(['user' => fn($q) => $q->withTrashed()])->orderBy('id', 'desc')->paginate(50);
        return view("admin.logs.index", compact("logs"));
    }
}
