<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Item extends Model {
    use LogsActivity;
    protected $fillable = ["category_id", "name", "image", "price", "mrp", "is_available", "stock_quantity", "low_stock_limit"];

    public function isReallyAvailable() {
        return $this->is_available && $this->stock_quantity > 0;
    }

    public function category() { return $this->belongsTo(Category::class); }
    public function variants() { return $this->hasMany(ItemVariant::class); }
    public function extras() { return $this->hasMany(ItemExtra::class); }
}