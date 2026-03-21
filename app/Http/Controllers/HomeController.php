<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Item;
use App\Repositories\OrderRepository;

class HomeController extends Controller {
    public function index() {
        $categories = Category::all();
        $items = Item::with(["category", "variants", "extras"])->where("is_available", 1)->where("stock_quantity", ">", 0)->get();
        $coupons = \App\Models\Coupon::where('show_on_home', 1)->where('coupon_type', '!=', 'Internal')->get();
        return view("welcome", compact("categories", "items", "coupons"));
    }

    public function placeOrder(Request $request, OrderRepository $repo) {
        try {
            // Default values for Customer self-order
            $data = $request->all();
            if(isset($data['items']) && is_string($data['items'])) {
                $data['items'] = json_decode($data['items'], true);
            }
            if(!isset($data["order_type"])) {
                $data["order_type"] = "Takeaway"; 
            }
            if(!isset($data["payment_method"])) {
                $data["payment_method"] = "Cash"; 
            }
            $data["payment_status"] = "Pending";
            
            $order = $repo->createOrder($data);
            return response()->json(["status" => true, "order_id" => $order->id, "order_number" => $order->order_number, "msg" => "Order placed successfully!"]);
        } catch (\Exception $e) {
            return response()->json(["status" => false, "msg" => $e->getMessage()]);
        }
    }

    public function orderSuccess($order_number) {
        $order = \App\Models\Order::with('items')->where('order_number', $order_number)->firstOrFail();
        return view('order-success', compact('order'));
    }
}
