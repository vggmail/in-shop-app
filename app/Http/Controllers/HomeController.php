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
        
        $customer = \App\Models\Customer::with('addresses')->find(session('customer_id'));
        $recentItems = collect();
        if($customer) {
            $orderIds = \App\Models\Order::where('customer_id', $customer->id)->pluck('id');
            $recentItemIds = \App\Models\OrderItem::whereIn('order_id', $orderIds)
                ->distinct()
                ->pluck('item_id');
            
            $recentItems = Item::with(['category', 'variants', 'extras'])
                ->whereIn('id', $recentItemIds)
                ->where('is_available', 1)
                ->where('stock_quantity', '>', 0)
                ->limit(10)
                ->get();
        }

        return view("welcome", compact("categories", "items", "coupons", "customer", "recentItems"));
    }

    public function placeOrder(Request $request, OrderRepository $repo) {
        try {
            \Illuminate\Support\Facades\Log::info("PlaceOrder: Received request", $request->all());
            $data = $request->all();
            if(isset($data['items']) && is_string($data['items'])) {
                $data['items'] = json_decode($data['items'], true);
            }
            $data["order_type"] = $data["order_type"] ?? "Takeaway"; 
            $data["payment_method"] = $data["payment_method"] ?? "Cash"; 
            $data["payment_status"] = "Pending";
            $data["customer_id"] = session("customer_id");
            
            \Illuminate\Support\Facades\Log::info("PlaceOrder: Creating order with method: " . $data["payment_method"]);
            $order = $repo->createOrder($data);
            \Illuminate\Support\Facades\Log::info("PlaceOrder: Order created successfully. Order #: " . $order->order_number);
            
            if ($order->payment_method == 'UPI' || $order->payment_method == 'PayU') {
                $tenant = app()->bound('tenant') ? app('tenant') : null;
                
                // If direct UPI is selected and we have a VPA (upi_id)
                if ($order->payment_method == 'UPI' && $tenant && $tenant->upi_id) {
                    $upi_vpa = $tenant->upi_id;
                    $shop_name = $tenant->name ?? 'Shop';
                    $order_msg = "Order #" . $order->order_number;
                    
                    // Standard UPI Deep Link: upi://pay?pa=VPA&pn=NAME&am=AMOUNT&cu=INR&tn=MESSAGE
                    $upi_url = "upi://pay?pa=" . $upi_vpa . "&pn=" . urlencode($shop_name) . "&am=" . $order->grand_total . "&cu=INR&tn=" . urlencode($order_msg);
                    
                    \Illuminate\Support\Facades\Log::info("PlaceOrder: Redirecting to UPI App: " . $upi_url);
                    return response()->json([
                        'status' => true,
                        'is_upi' => true,
                        'order_number' => $order->order_number,
                        'redirect_url' => $upi_url,
                        'data' => $order,
                        'message' => 'Opening UPI Apps...'
                    ]);
                }

                // Fallback to PayU for ONLINE or if UPI ID is missing
                $redirectUrl = route('payu.pay', $order->order_number);
                \Illuminate\Support\Facades\Log::info("PlaceOrder: Redirecting to PayU: " . $redirectUrl);
                return response()->json([
                    'status' => true,
                    'is_upi' => false,
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'redirect_url' => $redirectUrl,
                    'data' => $order,
                    'message' => 'Redirecting to payment gateway...'
                ]);
            }

            \Illuminate\Support\Facades\Log::info("PlaceOrder: Order completed for Cash/Offline.");
            return response()->json([
                'status' => true,
                'order_number' => $order->order_number,
                'data' => $order,
                'message' => 'Order placed successfully!'
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("PlaceOrder Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->sendError($e->getMessage(), 422);
        }
    }

    public function orderSuccess($order_number) {
        $order = \App\Models\Order::with('items')->where('order_number', $order_number)->firstOrFail();
        return view('order-success', compact('order'));
    }

    public function checkStatus($order_number) {
        $order = \App\Models\Order::where('order_number', $order_number)->first();
        if(!$order) return response()->json(['status' => false]);
        return response()->json([
            'status' => true,
            'payment_status' => $order->payment_status,
            'order_status' => $order->status
        ]);
    }
}
