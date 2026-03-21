<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Repositories\OrderRepository;
use App\Repositories\ItemRepository;
use App\Repositories\CustomerRepository;
use App\Models\Payment;

class OrderController extends Controller {
    protected $repo;
    protected $iRepo;
    protected $cRepo;
    
    public function __construct(OrderRepository $repo, ItemRepository $iRepo, CustomerRepository $cRepo) { 
        $this->repo = $repo; 
        $this->iRepo = $iRepo; 
        $this->cRepo = $cRepo; 
    }
    
    // POS Screen
    public function create() { 
        $items = $this->iRepo->getAll(); 
        $customers = $this->cRepo->getAll();
        return view("admin.pos.index", compact("items", "customers")); 
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
