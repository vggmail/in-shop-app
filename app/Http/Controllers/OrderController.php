<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Repositories\OrderRepository;
use App\Repositories\ItemRepository;
use App\Repositories\CustomerRepository;
use App\Models\Payment;
use App\Http\Requests\StoreOrderRequest;

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
    
    public function store(StoreOrderRequest $request) {
        try {
            $order = $this->repo->createOrder($request->validated());
            return response()->json([
                'status' => true,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'data' => $order,
                'message' => 'Order completed successfully'
            ]);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 422);
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

    public function checkPending() {
        $count = \App\Models\Order::where('status', '!=', 'Completed')->count();
        $latest = \App\Models\Order::orderBy('id', 'desc')->first();
        return response()->json([
            'count' => $count,
            'latest_id' => $latest ? $latest->id : 0,
            'latest_order' => $latest ? $latest->order_number : null
        ]);
    }

    public function updateStatus(Request $request, $id) {
        $order = \App\Models\Order::findOrFail($id);
        if ($request->has('status')) {
            $order->status = $request->status;
        }
        if ($request->has('payment_status')) {
            $order->payment_status = $request->payment_status;
        }
        $order->save();
        return back()->with('success', 'Order status updated successfully!');
    }
}
