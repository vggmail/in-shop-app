<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;

class AdminController extends Controller {
    public function dashboard() {
        $todaySales = Order::whereDate("created_at", date("Y-m-d"))->sum("grand_total");
        $totalOrders = Order::count();
        $totalRevenue = Order::sum("grand_total");
        $topItems = \App\Models\Item::orderBy("id", "desc")->take(5)->get();
        // Charts data (simplistic)
        $dailySales = Order::selectRaw("DATE(created_at) as date, SUM(grand_total) as total")
                        ->groupBy("date")->orderBy("date", "desc")->take(7)->get();
                        
        return view("admin.dashboard", compact("todaySales", "totalOrders", "totalRevenue", "topItems", "dailySales"));
    }
}
