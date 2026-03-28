<?php
$dirM = __DIR__ . '/app/Models';
$dirR = __DIR__ . '/app/Repositories';
$dirC = __DIR__ . '/app/Http/Controllers';

if(!is_dir($dirM)) mkdir($dirM, 0777, true);
if(!is_dir($dirR)) mkdir($dirR, 0777, true);
if(!is_dir($dirC)) mkdir($dirC, 0777, true);

// Models
$models = [
    'Item' => 'protected $fillable = ["category_id", "name", "image", "price", "is_available"];
    public function category() { return $this->belongsTo(Category::class); }
    public function variants() { return $this->hasMany(ItemVariant::class); }
    public function extras() { return $this->hasMany(ItemExtra::class); }',
    
    'ItemVariant' => 'protected $fillable = ["item_id", "name", "price"];
    public function item() { return $this->belongsTo(Item::class); }',
    
    'ItemExtra' => 'protected $fillable = ["item_id", "name", "price"];
    public function item() { return $this->belongsTo(Item::class); }',
    
    'Expense' => 'protected $fillable = ["category", "amount", "date", "description"];',
    
    'Order' => 'protected $fillable = ["order_number", "customer_id", "order_type", "table_number", "total_amount", "discount_amount", "grand_total", "payment_method", "payment_status", "status", "note"];
    public function customer() { return $this->belongsTo(Customer::class); }
    public function items() { return $this->hasMany(OrderItem::class); }
    public function payments() { return $this->hasMany(Payment::class); }',
    
    'OrderItem' => 'protected $fillable = ["order_id", "item_id", "item_variant_id", "price", "quantity", "total"];
    public function item() { return $this->belongsTo(Item::class); }
    public function variant() { return $this->belongsTo(ItemVariant::class, "item_variant_id"); }
    public function extras() { return $this->hasMany(OrderItemExtra::class); }',
    
    'OrderItemExtra' => 'protected $fillable = ["order_item_id", "item_extra_id", "price"];
    public function extra() { return $this->belongsTo(ItemExtra::class, "item_extra_id"); }',
];

foreach($models as $name => $body) {
    file_put_contents("$dirM/$name.php", "<?php\nnamespace App\Models;\nuse Illuminate\Database\Eloquent\Model;\nclass $name extends Model {\n    $body\n}");
}

// OrderRepository handling extras and variants
file_put_contents("$dirR/OrderRepository.php", '<?php
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
            
            $order = Order::create([
                "order_number" => $orderNum,
                "customer_id" => $data["customer_id"] ?? null,
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

            if(!empty($data["items"])) {
                foreach ($data["items"] as $item) {
                    $oi = OrderItem::create([
                        "order_id" => $order->id,
                        "item_id" => $item["item_id"],
                        "item_variant_id" => $item["variant_id"] ?? null,
                        "price" => $item["price"],
                        "quantity" => $item["quantity"],
                        "total" => $item["total"]
                    ]);
                    
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
            
            if(!empty($data["customer_id"])) {
                $customer = Customer::find($data["customer_id"]);
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
');

// ItemRepository
file_put_contents("$dirR/ItemRepository.php", '<?php
namespace App\Repositories;
use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\ItemExtra;

class ItemRepository {
    public function getAll() { return Item::with(["category", "variants", "extras"])->orderBy("id", "DESC")->get(); }
    public function find($id) { return Item::findOrFail($id); }
    public function create($data) { 
        $item = Item::create($data); 
        return $item;
    }
    public function update($id, $data) { $i = $this->find($id); $i->update($data); return $i; }
    public function delete($id) { return $this->find($id)->delete(); }
}
');

// ExpenseRepository
file_put_contents("$dirR/ExpenseRepository.php", '<?php
namespace App\Repositories;
use App\Models\Expense;

class ExpenseRepository {
    public function getAll() { return Expense::orderBy("date", "DESC")->get(); }
    public function create($data) { return Expense::create($data); }
    public function update($id, $data) { $e = Expense::findOrFail($id); $e->update($data); return $e; }
    public function delete($id) { return Expense::findOrFail($id)->delete(); }
}
');

echo "Models and Repos generated.\n";
