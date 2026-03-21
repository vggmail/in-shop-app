<?php
namespace App\Repositories;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemExtra;
use App\Models\Payment;
use App\Models\Customer;
use DB;

class OrderRepository {
    public function getAll() { return Order::with("customer")->orderBy("id", "DESC")->paginate(15); }
    public function find($id) { return Order::with(["customer", "items.item", "items.variant", "items.extras.extra", "payments"])->findOrFail($id); }
    
    public function createOrder($data) {
        DB::beginTransaction();
        try {
            $orderNum = "ORD-" . strtoupper(uniqid());
            
            $customerId = $data["customer_id"] ?? null;
            if (!$customerId && !empty($data["customer_phone"])) {
                $customer = Customer::firstOrCreate(
                    ["phone" => $data["customer_phone"]],
                    ["name" => $data["customer_name"] ?? "Guest Customer"]
                );
                $customerId = $customer->id;
            }

            $order = Order::create([
                "order_number" => $orderNum,
                "customer_id" => $customerId,
                "order_type" => $data["order_type"],
                "table_number" => $data["table_number"] ?? null,
                "note" => $data["note"] ?? null,
                "total_amount" => $data["total_amount"],
                "discount_amount" => $data["discount_amount"] ?? 0,
                "grand_total" => $data["grand_total"],
                "payment_method" => $data["payment_method"],
                "payment_status" => $data["grand_total"] > 0 && $data["payment_method"] != "Pending" ? "Paid" : "Pending",
                "status" => "Preparing",
            ]);

            if(!empty($data["items"]) && is_array($data["items"])) {
                foreach ($data["items"] as $item) {
                    $itemModel = \App\Models\Item::find($item["item_id"]);
                    if(!$itemModel || $itemModel->stock_quantity < $item["quantity"]) {
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
                    
                    if(!empty($item["extras"])) {
                        foreach($item["extras"] as $extra) {
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
            
            if($customerId) {
                $customer = Customer::find($customerId);
                if($customer) {
                    $customer->increment("total_orders");
                    $customer->increment("total_spending", $data["grand_total"]);
                }
            }

            DB::commit();
            return $order;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
