<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Repositories\ItemRepository;
use App\Models\Category;

class ItemController extends Controller {
    protected $repo;
    public function __construct(ItemRepository $repo) { $this->repo = $repo; }
    
    public function index() { 
        $items = $this->repo->getAll(); 
        $categories = Category::all();
        return view("admin.items.index", compact("items", "categories")); 
    }
    
    public function store(Request $request) {
        $this->repo->create($request->all());
        return redirect()->back()->with("success", "Item added successfully");
    }
    
    public function update(Request $request, $id) {
        $this->repo->update($id, $request->all());
        return redirect()->back()->with("success", "Item updated successfully");
    }
    
    public function destroy($id) {
        $this->repo->delete($id);
        return redirect()->back()->with("success", "Item deleted successfully");
    }
}
