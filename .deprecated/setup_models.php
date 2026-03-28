<?php
$dir = __DIR__ . '/app/Models';

file_put_contents("$dir/Role.php", '<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Role extends Model {
    protected $fillable = ["name"];
    public function users() { return $this->hasMany(User::class); }
}
');

file_put_contents("$dir/Category.php", '<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Category extends Model {
    protected $fillable = ["name", "slug"];
    public function products() { return $this->hasMany(Product::class); }
}
');

file_put_contents("$dir/Product.php", '<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Product extends Model {
    protected $fillable = ["category_id", "name", "sku", "price", "stock_quantity", "low_stock_alert"];
    public function category() { return $this->belongsTo(Category::class); }
}
');

file_put_contents("$dir/Customer.php", '<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Customer extends Model {
    protected $fillable = ["name", "phone", "email", "total_orders", "total_purchase"];
    public function orders() { return $this->hasMany(Order::class); }
}
');

file_put_contents("$dir/Coupon.php", '<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Coupon extends Model {
    protected $fillable = ["code", "discount_type", "value", "min_order_amount", "expiry_date"];
}
');

file_put_contents("$dir/Order.php", '<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Order extends Model {
    protected $fillable = ["order_number", "customer_id", "total_amount", "discount_amount", "grand_total", "payment_method", "payment_status", "order_status"];
    public function customer() { return $this->belongsTo(Customer::class); }
    public function items() { return $this->hasMany(OrderItem::class); }
    public function payments() { return $this->hasMany(Payment::class); }
}
');

file_put_contents("$dir/OrderItem.php", '<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OrderItem extends Model {
    protected $fillable = ["order_id", "product_id", "quantity", "price", "total"];
    public function product() { return $this->belongsTo(Product::class); }
    public function order() { return $this->belongsTo(Order::class); }
}
');

file_put_contents("$dir/Payment.php", '<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Payment extends Model {
    protected $fillable = ["order_id", "payment_method", "paid_amount", "payment_date", "payment_status"];
    public function order() { return $this->belongsTo(Order::class); }
}
');

file_put_contents("$dir/CouponUsage.php", '<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CouponUsage extends Model {
    protected $fillable = ["coupon_id", "order_id", "discount_amount"];
    public function coupon() { return $this->belongsTo(Coupon::class); }
    public function order() { return $this->belongsTo(Order::class); }
}
');

// For User.php, we need to preserve standard imports, just replace basic content.
$userContent = file_get_contents("$dir/User.php");
if (strpos($userContent, "role_id") === false) {
    $userContent = str_replace(
        "protected \$fillable = [",
        "protected \$fillable = [\n        'role_id',\n        'phone',",
        $userContent
    );
    $userContent = str_replace(
        "class User extends Authenticatable\n{",
        "class User extends Authenticatable\n{\n    public function role() { return \$this->belongsTo(Role::class); }",
        $userContent
    );
    file_put_contents("$dir/User.php", $userContent);
}

echo "Models setup complete.\n";
