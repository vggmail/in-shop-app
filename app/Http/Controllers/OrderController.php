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
            $data = $request->validated();
            $data['source'] = 'POS';
            $order = $this->repo->createOrder($data);
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
    
    public function index(Request $request) {
        $query = \App\Models\Order::with(['customer' => fn($q) => $q->withTrashed()])->orderBy('id', 'desc');

        if ($request->filled('customer_search')) {
            $search = $request->customer_search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($cq) use ($search) {
                      $cq->withTrashed()->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->paginate(15)->appends($request->all());
        
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
        $payments = Payment::with("order")->orderBy("id", "DESC")->paginate(20);
        $allPayments = Payment::with("order")->orderBy("id", "DESC")->get(); // for summary cards
        return view("admin.payments.index", compact("payments", "allPayments"));
    }

    public function paymentShow($id) {
        $payment = Payment::with(['order', 'order.customer'])->findOrFail($id);
        $attempts = \App\Models\PaymentAttempt::where('order_id', $payment->order_id)->orderBy('id', 'DESC')->get();
        return view("admin.payments.show", compact("payment", "attempts"));
    }

    public function exportPayments() {
        $payments = Payment::with("order")->orderBy("id", "DESC")->get();

        $filename = "payments_" . date("Y-m-d") . ".csv";
        $headers = [
            "Content-Type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($payments) {
            $out = fopen("php://output", "w");
            fputcsv($out, ["Order #", "Date", "Method", "Amount", "Status"]);
            foreach ($payments as $p) {
                fputcsv($out, [
                    "#ORD-" . ($p->order->id ?? "N/A"),
                    date("d M Y H:i", strtotime($p->created_at)),
                    $p->method,
                    number_format($p->amount, 2),
                    $p->status,
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
    
    public function reports() {
        return view("admin.reports.index");
    }

    public function checkPending() {
        $count = \App\Models\Order::where('status', '!=', 'Completed')->where('source', 'Online')->count();
        $latest = \App\Models\Order::where('source', 'Online')->orderBy('id', 'desc')->first();
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
