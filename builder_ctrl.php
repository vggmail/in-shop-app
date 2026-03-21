<?php

$dirCtrl = __DIR__ . '/app/Http/Controllers';

// AdminController
file_put_contents("$dirCtrl/AdminController.php", '<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;

class AdminController extends Controller {
    public function dashboard() {
        $todaySales = Order::whereDate("created_at", date("Y-m-d"))->sum("grand_total");
        $totalOrders = Order::count();
        $totalRevenue = Order::sum("grand_total");
        $topProducts = Product::orderBy("stock_quantity", "asc")->take(5)->get();
        // Charts data (simplistic)
        $dailySales = Order::selectRaw("DATE(created_at) as date, SUM(grand_total) as total")
                        ->groupBy("date")->orderBy("date", "desc")->take(7)->get();
                        
        return view("admin.dashboard", compact("todaySales", "totalOrders", "totalRevenue", "topProducts", "dailySales"));
    }
}
');

// ProductController
file_put_contents("$dirCtrl/ProductController.php", '<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Repositories\ProductRepository;
use App\Models\Category;

class ProductController extends Controller {
    protected $repo;
    public function __construct(ProductRepository $repo) { $this->repo = $repo; }
    
    public function index() { 
        $products = $this->repo->getAll(); 
        $categories = Category::all();
        return view("admin.products.index", compact("products", "categories")); 
    }
    
    public function store(Request $request) {
        $this->repo->create($request->all());
        return redirect()->back()->with("success", "Product added successfully");
    }
    
    public function update(Request $request, $id) {
        $this->repo->update($id, $request->except("_method", "_token"));
        return redirect()->back()->with("success", "Product updated successfully");
    }
    
    public function destroy($id) {
        $this->repo->delete($id);
        return redirect()->back()->with("success", "Product deleted successfully");
    }
}
');

// CustomerController
file_put_contents("$dirCtrl/CustomerController.php", '<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Repositories\CustomerRepository;

class CustomerController extends Controller {
    protected $repo;
    public function __construct(CustomerRepository $repo) { $this->repo = $repo; }
    
    public function index() { 
        $customers = $this->repo->getAll(); 
        return view("admin.customers.index", compact("customers")); 
    }
    
    public function store(Request $request) {
        $this->repo->create($request->all());
        return redirect()->back()->with("success", "Customer added successfully");
    }
    
    public function update(Request $request, $id) {
        $this->repo->update($id, $request->except("_method", "_token"));
        return redirect()->back()->with("success", "Customer updated successfully");
    }
    
    public function destroy($id) {
        $this->repo->delete($id);
        return redirect()->back()->with("success", "Customer deleted successfully");
    }
}
');

// CouponController
file_put_contents("$dirCtrl/CouponController.php", '<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Repositories\CouponRepository;

class CouponController extends Controller {
    protected $repo;
    public function __construct(CouponRepository $repo) { $this->repo = $repo; }
    
    public function index() { 
        $coupons = $this->repo->getAll(); 
        return view("admin.coupons.index", compact("coupons")); 
    }
    
    public function store(Request $request) {
        $this->repo->create($request->all());
        return redirect()->back()->with("success", "Coupon added successfully");
    }
    
    public function update(Request $request, $id) {
        $this->repo->update($id, $request->except("_method", "_token"));
        return redirect()->back()->with("success", "Coupon updated successfully");
    }
    
    public function destroy($id) {
        $this->repo->delete($id);
        return redirect()->back()->with("success", "Coupon deleted successfully");
    }
    
    public function check(Request $request) {
        $coupon = $this->repo->findByCode($request->code);
        if($coupon) {
            return response()->json(["status" => true, "coupon" => $coupon]);
        }
        return response()->json(["status" => false, "msg" => "Invalid Coupon"]);
    }
}
');

// OrderController
file_put_contents("$dirCtrl/OrderController.php", '<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\CustomerRepository;
use App\Models\Payment;

class OrderController extends Controller {
    protected $repo;
    protected $pRepo;
    protected $cRepo;
    
    public function __construct(OrderRepository $repo, ProductRepository $pRepo, CustomerRepository $cRepo) { 
        $this->repo = $repo; 
        $this->pRepo = $pRepo; 
        $this->cRepo = $cRepo; 
    }
    
    // POS Screen
    public function create() { 
        $products = $this->pRepo->getAll(); 
        $customers = $this->cRepo->getAll();
        return view("admin.pos.index", compact("products", "customers")); 
    }
    
    public function store(Request $request) {
        try {
            $order = $this->repo->createOrder($request->all());
            return response()->json(["status" => true, "order_id" => $order->id, "msg" => "Order completed successfully"]);
        } catch (\Exception $e) {
            return response()->json(["status" => false, "msg" => $e->getMessage()]);
        }
    }
    
    public function index() {
        $orders = $this->repo->getAll();
        return view("admin.orders.index", compact("orders"));
    }
    
    public function show($id) {
        $order = $this->repo->find($id);
        return view("admin.orders.show", compact("order"));
    }
    
    public function printInvoice($id) {
        $order = $this->repo->find($id);
        return view("admin.orders.invoice", compact("order"));
    }

    public function payments() {
        $payments = Payment::with("order")->orderBy("id", "DESC")->get();
        return view("admin.payments.index", compact("payments"));
    }
    
    public function reports() {
        return view("admin.reports.index");
    }
}
');

echo "Controllers Generated.\n";
