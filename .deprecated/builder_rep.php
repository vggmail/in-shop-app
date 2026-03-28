<?php

$dirRep = __DIR__ . '/app/Repositories';
if(!is_dir($dirRep)) mkdir($dirRep, 0777, true);

// ProductRepository
file_put_contents("$dirRep/ProductRepository.php", '<?php
namespace App\Repositories;
use App\Models\Product;

class ProductRepository {
    public function getAll() { return Product::with("category")->orderBy("id", "DESC")->get(); }
    public function getPaginated($limit = 10) { return Product::with("category")->orderBy("id", "DESC")->paginate($limit); }
    public function find($id) { return Product::findOrFail($id); }
    public function create($data) { return Product::create($data); }
    public function update($id, $data) { $p = $this->find($id); $p->update($data); return $p; }
    public function delete($id) { return $this->find($id)->delete(); }
    public function search($query) { return Product::where("name", "like", "%$query%")->orWhere("sku", "like", "%$query%")->get(); }
}
');

// CustomerRepository
file_put_contents("$dirRep/CustomerRepository.php", '<?php
namespace App\Repositories;
use App\Models\Customer;

class CustomerRepository {
    public function getAll() { return Customer::orderBy("id", "DESC")->get(); }
    public function find($id) { return Customer::findOrFail($id); }
    public function create($data) { return Customer::create($data); }
    public function update($id, $data) { $c = $this->find($id); $c->update($data); return $c; }
    public function delete($id) { return $this->find($id)->delete(); }
    public function search($query) { return Customer::where("name", "like", "%$query%")->orWhere("phone", "like", "%$query%")->get(); }
}
');

// CouponRepository
file_put_contents("$dirRep/CouponRepository.php", '<?php
namespace App\Repositories;
use App\Models\Coupon;

class CouponRepository {
    public function getAll() { return Coupon::orderBy("id", "DESC")->get(); }
    public function find($id) { return Coupon::findOrFail($id); }
    public function create($data) { return Coupon::create($data); }
    public function update($id, $data) { $c = $this->find($id); $c->update($data); return $c; }
    public function delete($id) { return $this->find($id)->delete(); }
    public function findByCode($code) { return Coupon::where("code", $code)->first(); }
}
');

// OrderRepository
file_put_contents("$dirRep/OrderRepository.php", '<?php
namespace App\Repositories;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Customer;
use DB;

class OrderRepository {
    public function getAll() { return Order::with("customer")->orderBy("id", "DESC")->paginate(10); }
    public function find($id) { return Order::with(["customer", "items.product", "payments"])->findOrFail($id); }
    
    public function createOrder($data) {
        DB::beginTransaction();
        try {
            // Generate Order Number
            $orderNum = "ORD-" . strtoupper(uniqid());
            
            $order = Order::create([
                "order_number" => $orderNum,
                "customer_id" => $data["customer_id"] ?? null,
                "total_amount" => $data["total_amount"],
                "discount_amount" => $data["discount_amount"] ?? 0,
                "grand_total" => $data["grand_total"],
                "payment_method" => $data["payment_method"],
                "payment_status" => $data["grand_total"] > 0 && $data["payment_method"] != "Pending" ? "Paid" : "Pending",
                "order_status" => "Completed",
            ]);

            foreach ($data["items"] as $item) {
                OrderItem::create([
                    "order_id" => $order->id,
                    "product_id" => $item["product_id"],
                    "quantity" => $item["quantity"],
                    "price" => $item["price"],
                    "total" => $item["total"]
                ]);
                
                // Reduce stock
                $product = Product::find($item["product_id"]);
                if($product) {
                    $product->decrement("stock_quantity", $item["quantity"]);
                }
            }

            Payment::create([
                "order_id" => $order->id,
                "payment_method" => $data["payment_method"],
                "paid_amount" => $data["grand_total"],
                "payment_date" => date("Y-m-d"),
                "payment_status" => $order->payment_status
            ]);
            
            if(!empty($data["customer_id"])) {
                $customer = Customer::find($data["customer_id"]);
                if($customer) {
                    $customer->increment("total_orders");
                    $customer->increment("total_purchase", $data["grand_total"]);
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
');

echo "Repositories Generated.\n";
