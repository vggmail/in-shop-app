<?php
namespace App\Repositories;
use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\ItemExtra;
use Illuminate\Support\Facades\DB;

class ItemRepository extends BaseRepository {
    public function __construct(Item $model)
    {
        parent::__construct($model);
    }

    public function getAll() { return $this->all(['*'], ["category", "variants", "extras"]); }
    
    public function create(array $data): \Illuminate\Database\Eloquent\Model { 
        return DB::transaction(function() use ($data) {
            $item = Item::create($data); 

            if(!empty($data['variants'])) {
                foreach($data['variants'] as $v) {
                    if(!empty($v['name'])) {
                        ItemVariant::create([
                            'item_id' => $item->id,
                            'name' => $v['name'],
                            'price' => $v['price'] ?? 0
                        ]);
                    }
                }
            }

            if(!empty($data['extras'])) {
                foreach($data['extras'] as $e) {
                    if(!empty($e['name'])) {
                        ItemExtra::create([
                            'item_id' => $item->id,
                            'name' => $e['name'],
                            'price' => $e['price'] ?? 0
                        ]);
                    }
                }
            }
            return $item;
        });
    }

    public function update(int $id, array $data): bool { 
        return DB::transaction(function() use ($id, $data) {
            $i = $this->find($id); 
            $i->update($data); 

            // Refresh variants
            $i->variants()->delete();
            if(!empty($data['variants'])) {
                foreach($data['variants'] as $v) {
                    if(!empty($v['name'])) {
                        ItemVariant::create([
                            'item_id' => $i->id,
                            'name' => $v['name'],
                            'price' => $v['price'] ?? 0
                        ]);
                    }
                }
            }

            // Refresh extras
            $i->extras()->delete();
            if(!empty($data['extras'])) {
                foreach($data['extras'] as $e) {
                    if(!empty($e['name'])) {
                        ItemExtra::create([
                            'item_id' => $i->id,
                            'name' => $e['name'],
                            'price' => $e['price'] ?? 0
                        ]);
                    }
                }
            }
            return true;
        });
    }

    public function delete(int $id): bool { return $this->find($id)->delete(); }
}
