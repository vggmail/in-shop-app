<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model {
    use SoftDeletes;
    protected $fillable = ["name", "slug", "parent_id", "image", "is_active"];

    public function parent() {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children() {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function items() {
        return $this->hasMany(Item::class);
    }

    public function getFullNameAttribute() {
        return $this->parent ? $this->parent->name . ' > ' . $this->name : $this->name;
    }
}
