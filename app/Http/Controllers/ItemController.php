<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Repositories\ItemRepository;
use App\Models\Category;
use App\Models\Item;

use App\Traits\ImageHandler;

class ItemController extends Controller {
    use ImageHandler;
    protected $repo;
    public function __construct(ItemRepository $repo) { $this->repo = $repo; }
    
    public function index() { 
        $items = Item::with(['category.parent', 'variants', 'extras'])->get(); 
        $categories = Category::with('parent')->get();
        return view("admin.items.index", compact("items", "categories")); 
    }
    
    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048'
        ]);
        
        $data = $request->except('image');
        if ($request->hasFile('image')) {
            list($imagePath, $thumbPath) = $this->processItemImage($request->file('image'));
            $data['image'] = $imagePath;
            $data['thumbnail'] = $thumbPath;
        }
        $this->repo->create($data);
        return redirect()->back()->with("success", "Item added successfully");
    }
    
    public function update(Request $request, $id) {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048'
        ]);

        $data = $request->except('image');
        $item = Item::findOrFail($id);

        if ($request->hasFile('image')) {
            list($imagePath, $thumbPath) = $this->processItemImage($request->file('image'), 'items', $item->image);
            // Also delete old thumbnail if exists
            if($item->thumbnail) \Illuminate\Support\Facades\Storage::disk('public')->delete($item->thumbnail);
            
            $data['image'] = $imagePath;
            $data['thumbnail'] = $thumbPath;
        }
        
        $this->repo->update($id, $data);
        return redirect()->back()->with("success", "Item updated successfully");
    }
    
    public function destroy($id) {
        $this->repo->delete($id);
        return redirect()->back()->with("success", "Item deleted successfully");
    }

    public function sampleCsv() {
        $headers = ['category_name', 'name', 'description', 'default_size', 'price', 'mrp', 'stock_quantity', 'low_stock_limit', 'is_available', 'variants', 'extras'];
        $callback = function() use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fputcsv($file, ['Burgers', 'Premium Cheese Burger', 'Delicious burger with extra cheese', 'Regular', '99.00', '120.00', '50', '10', '1', 'Large:50|Double Patty:90', 'Extra Cheese:20|Bacon:40']);
            fputcsv($file, ['Drinks', 'Cola', 'Cold bubbly drink', '500ml', '45.00', '50.00', '100', '20', '1', '', '']);
            fclose($file);
        };
        return response()->stream($callback, 200, [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=sample_items.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ]);
    }

    public function bulkUpload(Request $request) {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:2048'
        ]);

        $file = $request->file('file');
        $handle = fopen($file->path(), 'r');
        $header = fgetcsv($handle);
        
        // Remove BOM and trim headers to ensure exact matches
        if ($header) {
            $header = array_map(function($h) {
                return trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h));
            }, $header);
        }
        
        $rowCount = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (count($header) !== count($row)) continue;
            
            $data = array_combine($header, $row);
            $category_name = $data['category_name'] ?? null;
            $name = $data['name'] ?? null;
            
            if (!$category_name || !$name) continue;
            
            $category = Category::firstOrCreate(
                ['name' => trim($category_name)],
                ['slug' => \Illuminate\Support\Str::slug(trim($category_name))]
            );

            // Process Variants
            $variants = [];
            if (!empty($data['variants'])) {
                $vPairs = explode('|', $data['variants']);
                foreach ($vPairs as $vPair) {
                    $parts = explode(':', $vPair);
                    if (count($parts) >= 1 && trim($parts[0]) !== '') {
                        $variants[] = [
                            'name' => trim($parts[0]),
                            'price' => isset($parts[1]) ? (float)trim($parts[1]) : 0
                        ];
                    }
                }
            }

            // Process Extras
            $extras = [];
            if (!empty($data['extras'])) {
                $ePairs = explode('|', $data['extras']);
                foreach ($ePairs as $ePair) {
                    $parts = explode(':', $ePair);
                    if (count($parts) >= 1 && trim($parts[0]) !== '') {
                        $extras[] = [
                            'name' => trim($parts[0]),
                            'price' => isset($parts[1]) ? (float)trim($parts[1]) : 0
                        ];
                    }
                }
            }
            
            $this->repo->create([
                'category_id' => $category->id,
                'name' => trim($name),
                'description' => trim($data['description'] ?? ''),
                'default_size' => trim($data['default_size'] ?? ''),
                'price' => $data['price'] ?? 0,
                'mrp' => $data['mrp'] ?? null,
                'stock_quantity' => $data['stock_quantity'] ?? 0,
                'low_stock_limit' => $data['low_stock_limit'] ?? 10,
                'is_available' => $data['is_available'] ?? 1,
                'variants' => $variants,
                'extras' => $extras,
            ]);
            $rowCount++;
        }
        fclose($handle);
        return redirect()->back()->with('success', "Bulk uploaded {$rowCount} items successfully!");
    }
}
