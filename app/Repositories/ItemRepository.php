<?php
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
