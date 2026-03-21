<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Repositories\ProductRepository;
use App\Models\Category;

class ProductController extends Controller {
    protected $repo;
    public function __construct(ProductRepository $repo) { $this->repo = $repo; }
    
    public function index() { 
        $products = $this->repo->getAll(); 
        $categories = Category::all();
        return view("admin.products.index", compact("products", "categories")); 
    }
    
    public function store(Request $request) {
        $this->repo->create($request->all());
        return redirect()->back()->with("success", "Product added successfully");
    }
    
    public function update(Request $request, $id) {
        $this->repo->update($id, $request->except("_method", "_token"));
        return redirect()->back()->with("success", "Product updated successfully");
    }
    
    public function destroy($id) {
        $this->repo->delete($id);
        return redirect()->back()->with("success", "Product deleted successfully");
    }
}
