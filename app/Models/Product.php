<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Product extends Model {
    protected $fillable = ["category_id", "name", "sku", "price", "stock_quantity", "low_stock_alert"];
    public function category() { return $this->belongsTo(Category::class); }
}
