<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Item;
use App\Repositories\OrderRepository;

class HomeController extends Controller {
    public function index() {
        $categories = Category::all();
        $items = Item::with(["category", "variants", "extras"])->where("is_available", 1)->get();
        return view("welcome", compact("categories", "items"));
    }

    public function placeOrder(Request $request, OrderRepository $repo) {
        try {
            // Default values for Customer self-order
            $data = $request->all();
            $data["order_type"] = "Takeaway"; // or Dine-in if scan from table
            $data["payment_method"] = "Cash"; // or Redirect to stripe/paypal
            
            $order = $repo->createOrder($data);
            return response()->json(["status" => true, "order_id" => $order->id, "msg" => "Order placed successfully!"]);
        } catch (\Exception $e) {
            return response()->json(["status" => false, "msg" => $e->getMessage()]);
        }
    }
}
