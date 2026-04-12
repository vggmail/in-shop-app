<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

class Item extends Model {
    use LogsActivity, SoftDeletes;
    protected $fillable = ["category_id", "name", "slug", "description", "default_size", "image", "thumbnail", "price", "mrp", "is_available", "stock_quantity", "low_stock_limit"];

    protected static function boot() {
        parent::boot();
        static::saving(function($item) {
            if (empty($item->slug)) {
                $slug = \Illuminate\Support\Str::slug($item->name);
                $originalSlug = $slug;
                $count = 1;
                while (static::where('slug', $slug)->where('id', '!=', $item->id)->exists()) {
                    $slug = $originalSlug . '-' . $count++;
                }
                $item->slug = $slug;
            }
        });
    }

    public function isReallyAvailable() {
        return $this->is_available && $this->stock_quantity > 0;
    }

    public function category() { return $this->belongsTo(Category::class); }
    public function variants() { return $this->hasMany(ItemVariant::class); }
    public function extras() { return $this->hasMany(ItemExtra::class); }

    public function images() { return $this->hasMany(ItemImage::class)->orderBy('is_feature', 'desc')->orderBy('sort_order', 'asc'); }
    
    public function featureImage() {
        return $this->images()->where('is_feature', true)->first() ?: $this->images()->first();
    }

    public function getFeatureImageAttribute() {
        $feature = $this->featureImage();
        return $feature ? $feature->image_path : $this->image;
    }

    public function getFeatureThumbAttribute() {
        $feature = $this->featureImage();
        return $feature ? ($feature->thumbnail_path ?: $feature->image_path) : ($this->thumbnail ?: $this->image);
    }
}