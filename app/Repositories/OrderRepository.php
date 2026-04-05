<?php
namespace App\Repositories;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemExtra;
use App\Models\Payment;
use App\Models\Customer;
use DB;

class OrderRepository extends BaseRepository
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function getAll()
    {
        return $this->paginate(15, ["*"], ["customer"]);
    }
    public function find(int $id, array $relations = []): ?\Illuminate\Database\Eloquent\Model
    {
        return parent::find($id, [
            "customer" => fn($q) => $q->withTrashed(),
            "items.item" => fn($q) => $q->withTrashed(),
            "items.variant",
            "items.extras.extra",
            "payments"
        ]);
    }

    public function createOrder($data)
    {
        \Illuminate\Support\Facades\Log::info("OrderRepo: Creating order for customer phone: " . ($data["customer_phone"] ?? 'N/A'));
        DB::beginTransaction();
        try {
            $orderNum = "ORD-" . date('YmdHis') . rand(10, 99);

            $customerId = $data["customer_id"] ?? null;
            if (!$customerId && !empty($data["customer_phone"])) {
                $customerName = !empty($data["customer_name"]) ? $data["customer_name"] : "Guest Customer";
                $customer = Customer::updateOrCreate(
                    ["phone" => $data["customer_phone"]],
                    ["name" => $customerName]
                );
                $customerId = $customer->id;
                \Illuminate\Support\Facades\Log::info("OrderRepo: Customer created/found ID: " . $customerId);
            }

            $isOnline = in_array($data["payment_method"], ["PayU", "UPI", "Online"]);
            $order = Order::create([
                "order_number" => $orderNum,
                "customer_id" => $customerId,
                "coupon_id" => $data["coupon_id"] ?? null,
                "order_type" => $data["order_type"],
                "source" => $data["source"] ?? "Online",
                "table_number" => $data["table_number"] ?? null,
                "delivery_address" => $data["delivery_address"] ?? null,
                "note" => $data["note"] ?? null,
                "total_amount" => $data["total_amount"],
                "discount_amount" => $data["discount_amount"] ?? 0,
                "grand_total" => $data["grand_total"],
                "payment_method" => $data["payment_method"],
                "payment_status" => $data["payment_status"] ?? ($data["grand_total"] > 0 && $data["payment_method"] != "Pending" && !$isOnline ? "Paid" : "Pending"),
                "status" => $isOnline ? "Pending Payment" : "Preparing",
            ]);
            \Illuminate\Support\Facades\Log::info("OrderRepo: Order record created ID: " . $order->id . " Num: " . $orderNum);

            if (!empty($data["items"]) && is_array($data["items"])) {
                foreach ($data["items"] as $item) {
                    $itemModel = \App\Models\Item::find($item["item_id"]);
                    if (!$itemModel || $itemModel->stock_quantity < $item["quantity"]) {
                        \Illuminate\Support\Facades\Log::error("OrderRepo Error: Insufficient stock for Item ID: " . ($item["item_id"] ?? 'Unknown'));
                        throw new \Exception("Item '" . ($itemModel ? $itemModel->name : "Unknown") . "' is out of stock or insufficient quantity!");
                    }

                    $oi = OrderItem::create([
                        "order_id" => $order->id,
                        "item_id" => $item["item_id"],
                        "item_variant_id" => $item["variant_id"] ?? null,
                        "price" => $item["price"],
                        "quantity" => $item["quantity"],
                        "total" => $item["total"]
                    ]);

                    // Reduce Stock
                    $itemModel->decrement("stock_quantity", $item["quantity"]);

                    if (!empty($item["extras"])) {
                        foreach ($item["extras"] as $extra) {
                            OrderItemExtra::create([
                                "order_item_id" => $oi->id,
                                "item_extra_id" => $extra["id"],
                                "price" => $extra["price"]
                            ]);
                        }
                    }
                }
            }

            Payment::create([
                "order_id" => $order->id,
                "method" => $data["payment_method"],
                "amount" => $data["grand_total"],
                "date" => date("Y-m-d"),
                "status" => $order->payment_status
            ]);
            \Illuminate\Support\Facades\Log::info("OrderRepo: Payment record created for Order ID: " . $order->id);

            if ($customerId) {
                $customer = Customer::find($customerId);
                if ($customer) {
                    $customer->increment("total_orders");
                    $customer->increment("total_spending", $data["grand_total"]);

                    if (!empty($data["save_address"]) && !empty($data["street_address"])) {
                        \App\Models\CustomerAddress::updateOrCreate(
                            [
                                'customer_id' => $customer->id,
                                'street_address' => $data["street_address"],
                                'city' => $data["city"] ?? '',
                                'pincode' => $data["pincode"] ?? ''
                            ],
                            [
                                'state' => $data["state"] ?? '',
                                'label' => $data["address_label"] ?? 'Home',
                                'is_default' => $customer->addresses->count() == 0 // First address is default
                            ]
                        );
                    }
                }
            }

            DB::commit();
            \Illuminate\Support\Facades\Log::info("OrderRepo: Transaction committed successfully for Order: " . $orderNum);
            return $order;
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("OrderRepo Exception: " . $e->getMessage());
            throw $e;
        }
    }
}
