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

            if (!empty($data['image'])) {
                \App\Models\ItemImage::create([
                    'item_id' => $item->id,
                    'image_path' => $data['image'],
                    'thumbnail_path' => $data['thumbnail'] ?? null,
                    'is_feature' => true,
                    'sort_order' => 0
                ]);
            }

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
        return \Illuminate\Support\Facades\DB::transaction(function() use ($id, $data) {
            
            $i = $this->find($id);
            if (!$i) {
                \Illuminate\Support\Facades\Log::error("Item ID: $id not found in repository update");
                return false;
            }
            
            $i->description = $data['description'];
            
            $i->update($data); 
        
            if (isset($data['image'])) {
                // For future scope: mark all others as non-feature
                $i->images()->update(['is_feature' => false]);
                
                \App\Models\ItemImage::create([
                    'item_id' => $id,
                    'image_path' => $data['image'],
                    'thumbnail_path' => $data['thumbnail'] ?? null,
                    'is_feature' => true,
                    'sort_order' => 0
                ]);
            }

            // Only update variants if they are present in the data to avoid accidental deletion
            if (isset($data['variants'])) {
                $i->variants()->delete();
                foreach($data['variants'] as $v) {
                    if(!empty($v['name'])) {
                        \App\Models\ItemVariant::create([
                            'item_id' => $i->id,
                            'name' => $v['name'],
                            'price' => $v['price'] ?? 0
                        ]);
                    }
                }
            }

            // Only update extras if they are present in the data
            if (isset($data['extras'])) {
                $i->extras()->delete();
                foreach($data['extras'] as $e) {
                    if(!empty($e['name'])) {
                        \App\Models\ItemExtra::create([
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
