<?php
$dirC = __DIR__ . '/app/Http/Controllers';

file_put_contents("$dirC/ItemController.php", '<?php
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
    
    public function destroy($id) {
        $this->repo->delete($id);
        return redirect()->back()->with("success", "Item deleted successfully");
    }
}
');

file_put_contents("$dirC/ExpenseController.php", '<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Repositories\ExpenseRepository;

class ExpenseController extends Controller {
    protected $repo;
    public function __construct(ExpenseRepository $repo) { $this->repo = $repo; }
    
    public function index() { 
        $expenses = $this->repo->getAll(); 
        return view("admin.expenses.index", compact("expenses")); 
    }
    
    public function store(Request $request) {
        $this->repo->create($request->all());
        return redirect()->back()->with("success", "Expense added successfully");
    }
    
    public function destroy($id) {
        $this->repo->delete($id);
        return redirect()->back()->with("success", "Expense deleted successfully");
    }
}
');

$admin = file_get_contents("$dirC/AdminController.php");
$admin = str_replace('Product::', 'App\Models\Item::', $admin);
$admin = str_replace('topProducts', 'topItems', $admin);
file_put_contents("$dirC/AdminController.php", $admin);

$order = file_get_contents("$dirC/OrderController.php");
$order = str_replace('ProductRepository', 'ItemRepository', $order);
$order = str_replace('Product', 'Item', $order);
$order = str_replace('pRepo', 'iRepo', $order);
$order = str_replace('products', 'items', $order);
file_put_contents("$dirC/OrderController.php", $order);

// Web routes
$routes = <<<'EOL'
<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ExpenseController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    Route::resource('items', ItemController::class)->except(['create', 'show', 'edit']);
    Route::resource('customers', CustomerController::class)->except(['create', 'show', 'edit']);
    Route::resource('expenses', ExpenseController::class)->except(['create', 'show', 'edit']);
    
    Route::resource('coupons', CouponController::class)->except(['create', 'show', 'edit']);
    Route::post('coupons/check', [CouponController::class, 'check'])->name('coupons.check');
    
    Route::get('pos', [OrderController::class, 'create'])->name('pos.index');
    Route::post('pos/store', [OrderController::class, 'store'])->name('pos.store');
    
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{id}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('orders/{id}/invoice', [OrderController::class, 'printInvoice'])->name('orders.invoice');
    
    Route::get('payments', [OrderController::class, 'payments'])->name('payments.index');
    Route::get('reports', [OrderController::class, 'reports'])->name('reports.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
EOL;

file_put_contents(__DIR__ . '/routes/web.php', $routes);
echo "Controllers and Routes updated.\n";
