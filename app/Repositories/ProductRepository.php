<?php
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
