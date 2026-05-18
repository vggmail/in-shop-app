<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Repositories\OrderRepository;
use App\Repositories\ItemRepository;
use App\Repositories\CustomerRepository;
use App\Models\Payment;
use App\Http\Requests\StoreOrderRequest;

class OrderController extends Controller
{
    protected $repo;
    protected $iRepo;
    protected $cRepo;

    public function __construct(OrderRepository $repo, ItemRepository $iRepo, CustomerRepository $cRepo)
    {
        $this->repo = $repo;
        $this->iRepo = $iRepo;
        $this->cRepo = $cRepo;
    }

    // POS Screen
    public function create()
    {
        $items = $this->iRepo->getAll();
        $customers = $this->cRepo->getAll();

        // Top 16 items by order frequency (recent 90 days), hide out of stock, fallback to newest 16
        $topItemIds = \App\Models\OrderItem::select('item_id', \Illuminate\Support\Facades\DB::raw('count(*) as order_count'))
            ->whereHas('order', function($q) {
                $q->whereNotIn('status', ['Pending Payment', 'Payment Failed', 'Cancelled']);
            })
            ->where('created_at', '>=', now()->subDays(90))
            ->whereNotNull('item_id')
            ->whereHas('item', function ($q) {
                $q->where('stock_quantity', '>', 0);
            })
            ->groupBy('item_id')
            ->orderByDesc('order_count')
            ->limit(16)
            ->pluck('item_id');

        if ($topItemIds->count() >= 4) {
            $topItems = $items->whereIn('id', $topItemIds)->sortByDesc(function ($item) use ($topItemIds) {
                return array_search($item->id, $topItemIds->values()->toArray()) * -1;
            })->values();
        } else {
            // Fallback: take last 16 in-stock items
            $topItems = $items->where('stock_quantity', '>', 0)->sortByDesc('id')->take(16)->values();
        }

        $activeHappyHour = \App\Models\HappyHour::where('is_active', true)->get()->first(function($hh) {
            return $hh->isActiveNow();
        });

        return view("admin.pos.index", compact("items", "customers", "topItems", "activeHappyHour"));
    }

    public function store(StoreOrderRequest $request)
    {
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

    public function index(Request $request)
    {
        $query = \App\Models\Order::with(['customer' => fn($q) => $q->withTrashed()])->orderBy('id', 'desc');

        if ($request->filled('customer_search')) {
            $search = $request->customer_search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->withTrashed()->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        $orders = $query->paginate(15)->appends($request->all());

        return view("admin.orders.index", compact("orders"));
    }

    public function show($id)
    {
        $order = $this->repo->find($id);
        $tenant = app()->bound('tenant') ? app('tenant') : \App\Models\Tenant::first();
        return view("admin.orders.show", compact("order", "tenant"));
    }

    public function printInvoice($id)
    {
        $order = $this->repo->find($id);
        $tenant = app()->bound('tenant') ? app('tenant') : \App\Models\Tenant::first();
        if ($order->order_type == 'Home Delivery') {
            return view("admin.orders.delivery_invoice", compact("order", "tenant"));
        }
        return view("admin.orders.invoice", compact("order", "tenant"));
    }

    public function payments()
    {
        $payments = Payment::with("order")->orderBy("id", "DESC")->paginate(20);
        $allPayments = Payment::with("order")->orderBy("id", "DESC")->get(); // for summary cards
        return view("admin.payments.index", compact("payments", "allPayments"));
    }

    public function paymentShow($id)
    {
        $payment = Payment::with(['order', 'order.customer'])->findOrFail($id);
        $attempts = \App\Models\PaymentAttempt::where('order_id', $payment->order_id)->orderBy('id', 'DESC')->get();
        return view("admin.payments.show", compact("payment", "attempts"));
    }

    public function exportPayments()
    {
        $payments = Payment::with("order")->orderBy("id", "DESC")->get();

        $filename = "payments_" . date("Y-m-d") . ".csv";
        $headers = [
            "Content-Type" => "text/csv",
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

    public function reports(Request $request)
    {
        $tab = $request->query('tab', 'classic'); // Default to standard view
        $range = $request->query('range', '30'); // Default 30 days
        
        if ($tab === 'analytics') {
            $startDate = now()->subDays((int)$range)->startOfDay();
            
            // Core Metrics
            $grossSales = \App\Models\Order::whereNotIn('status', ['Pending Payment', 'Payment Failed', 'Cancelled'])
                ->where('created_at', '>=', $startDate)->sum('grand_total');
            $totalExpenses = \App\Models\Expense::where('date', '>=', $startDate)->sum('amount');
            $netProfit = $grossSales - $totalExpenses;
            $orderCount = \App\Models\Order::whereNotIn('status', ['Pending Payment', 'Payment Failed', 'Cancelled'])
                ->where('created_at', '>=', $startDate)->count();

            // Time-series for Line Chart
            $chartDates = [];
            $salesData = [];
            $expensesData = [];
            
            for ($i = (int)$range - 1; $i >= 0; $i--) {
                $dateStr = now()->subDays($i)->format('Y-m-d');
                $chartDates[] = now()->subDays($i)->format('d M');
                
                $salesData[] = \App\Models\Order::whereNotIn('status', ['Pending Payment', 'Payment Failed', 'Cancelled'])
                    ->whereDate('created_at', $dateStr)->sum('grand_total');
                $expensesData[] = \App\Models\Expense::whereDate('date', $dateStr)->sum('amount');
            }

            // Top Items for Doughnut Chart
            $topItemsQuery = \Illuminate\Support\Facades\DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('items', 'order_items.item_id', '=', 'items.id')
                ->whereNotIn('orders.status', ['Pending Payment', 'Payment Failed', 'Cancelled'])
                ->where('orders.created_at', '>=', $startDate)
                ->select(\Illuminate\Support\Facades\DB::raw('items.name, sum(order_items.quantity) as total_qty'))
                ->groupBy('items.id', 'items.name')
                ->orderByDesc('total_qty')
                ->limit(5)
                ->get();
                
            $topItemLabels = $topItemsQuery->pluck('name')->toArray();
            $topItemData = $topItemsQuery->pluck('total_qty')->toArray();

            return view("admin.reports.analytics", compact(
                'range', 'grossSales', 'totalExpenses', 'netProfit', 'orderCount', 
                'chartDates', 'salesData', 'expensesData', 'topItemLabels', 'topItemData', 'tab'
            ));
        }

        // Classic Dashboard Metrics
        $grossSalesTotal = \App\Models\Order::whereNotIn('status', ['Pending Payment', 'Payment Failed', 'Cancelled'])->sum("grand_total");
        $totalExpensesTotal = \App\Models\Expense::sum("amount");
        $netProfitTotal = $grossSalesTotal - $totalExpensesTotal;
        $orderCountTotal = \App\Models\Order::whereNotIn('status', ['Pending Payment', 'Payment Failed', 'Cancelled'])->count();
        
        $recentOrders = \App\Models\Order::whereNotIn('status', ['Pending Payment', 'Payment Failed', 'Cancelled'])
            ->latest()->take(10)->get();
        $recentExpenses = \App\Models\Expense::latest()->take(10)->get();

        return view("admin.reports.index", compact(
            'tab', 'grossSalesTotal', 'totalExpensesTotal', 'netProfitTotal', 
            'orderCountTotal', 'recentOrders', 'recentExpenses'
        ));
    }

    public function checkPending()
    {
        $count = \App\Models\Order::where('status', '!=', 'Completed')->where('status', '!=', 'Pending Payment')->where('source', 'Online')->count();
        $latest = \App\Models\Order::where('source', 'Online')->where('status', '!=', 'Pending Payment')->orderBy('id', 'desc')->first();
        return response()->json([
            'count' => $count,
            'latest_id' => $latest ? $latest->id : 0,
            'latest_order' => $latest ? $latest->order_number : null,
            'latest_status' => $latest ? $latest->status : null
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $order = \App\Models\Order::findOrFail($id);
        if ($request->has('status')) {
            $order->status = $request->status;
            
            // Only send status updates for Home Delivery
            if ($order->order_type == 'Home Delivery') {
                $this->repo->sendOrderStatusEmail($order);
                $this->repo->sendOrderWhatsAppNotification($order);
            }
        }
        if ($request->has('payment_status')) {
            $order->payment_status = $request->payment_status;
            
            // If admin marks as Paid, send the email
            if ($order->payment_status == 'Paid') {
                $this->repo->sendInvoiceEmail($order);
                $this->repo->sendOrderWhatsAppNotification($order);
            }
        }
        $order->save();
        return back()->with('success', 'Order status updated successfully!');
    }

    public function cancelOrder(Request $request, $id)
    {
        $order = \App\Models\Order::with('payments')->findOrFail($id);

        // Prevent cancelling already-cancelled/completed orders
        if (in_array($order->status, ['Cancelled', 'Completed'])) {
            return back()->with('error', 'This order cannot be cancelled (status: ' . $order->status . ').');
        }

        $order->status = 'Cancelled';
        $order->save();

        $refundResult = null;

        // Auto-refund only for online PayU payments that were successfully paid
        $payment = $order->payments()->where('method', 'PayU')->where('status', 'Paid')->first();

        if ($payment && $payment->transaction_id) {
            $refundResult = $this->initiatePayURefund($order, $payment);
        }

        if ($refundResult && isset($refundResult['status']) && $refundResult['status'] === true) {
            return back()->with('success', 'Order cancelled and refund of ₹' . number_format($payment->amount, 2) . ' initiated successfully. Refund ID: ' . ($refundResult['refund_id'] ?? 'N/A'));
        }

        if ($payment && $payment->transaction_id && !$refundResult) {
            return back()->with('warning', 'Order cancelled, but the refund could not be processed automatically. Please refund manually via PayU dashboard.');
        }

        return back()->with('success', 'Order has been cancelled successfully.');
    }

    /**
     * Initiate a PayU refund for a paid online order.
     */
    private function initiatePayURefund(\App\Models\Order $order, \App\Models\Payment $payment): array
    {
        $tenant = app()->bound('tenant') ? app('tenant') : \App\Models\Tenant::first();
        $gateway = \App\Models\PaymentGateway::where('tenant_id', $tenant->id)
            ->where('gateway_name', 'PayU')
            ->first();

        $key  = $gateway->settings['key']  ?? config('services.payu.key');
        $salt = $gateway->settings['salt'] ?? config('services.payu.salt');
        $mode = $gateway->settings['mode'] ?? 'test';

        $baseUrl = $mode === 'live'
            ? 'https://info.payu.in/merchant/postservice.php?form=2'
            : 'https://test.payu.in/merchant/postservice.php?form=2';

        $mihpayid     = $payment->transaction_id;
        $refundAmount = number_format($payment->amount, 2, '.', '');
        $tokenId      = uniqid('refund_', true);

        // PayU refund hash: key|command|var1|salt
        $command    = 'cancel_refund_transaction';
        $hashString = $key . '|' . $command . '|' . $mihpayid . '|' . $salt;
        $hash       = strtolower(hash('sha512', $hashString));

        $postData = [
            'key'     => $key,
            'command' => $command,
            'var1'    => $mihpayid,        // PayU transaction ID
            'var2'    => $tokenId,         // Unique refund token
            'var3'    => $refundAmount,    // Refund amount
            'hash'    => $hash,
        ];

        try {
            $ch = curl_init($baseUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            $curlError = curl_error($ch);
            curl_close($ch);

            \Illuminate\Support\Facades\Log::info("PayU Refund Request for Order #{$order->order_number}", $postData);
            \Illuminate\Support\Facades\Log::info("PayU Refund Response for Order #{$order->order_number}: " . $response);

            if ($curlError) {
                \Illuminate\Support\Facades\Log::error("PayU Refund cURL Error: " . $curlError);
                return ['status' => false, 'message' => 'cURL error: ' . $curlError];
            }

            $result = json_decode($response, true);

            // PayU returns status 1 for queued/success
            if (isset($result['status']) && $result['status'] == 1) {
                $payment->update([
                    'status'        => 'Refunded',
                    'refund_id'     => $result['request_id'] ?? $tokenId,
                    'refund_status' => 'Initiated',
                    'refund_amount' => $payment->amount,
                ]);
                return ['status' => true, 'refund_id' => $result['request_id'] ?? $tokenId, 'raw' => $result];
            }

            // Refund queued or needs manual review
            $payment->update([
                'refund_status' => 'Failed',
                'refund_id'     => $tokenId,
            ]);

            return ['status' => false, 'message' => $result['msg'] ?? 'Refund failed.', 'raw' => $result];

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("PayU Refund Exception: " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}
