<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Repositories\CustomerRepository;

class CustomerController extends Controller {
    protected $repo;
    public function __construct(CustomerRepository $repo) { $this->repo = $repo; }
    
    public function index() { 
        $customers = $this->repo->getAll(); 
        return view("admin.customers.index", compact("customers")); 
    }
    
    public function store(Request $request) {
        $this->repo->create($request->all());
        return redirect()->back()->with("success", "Customer added successfully");
    }
    
    public function update(Request $request, $id) {
        $this->repo->update($id, $request->except("_method", "_token"));
        return redirect()->back()->with("success", "Customer updated successfully");
    }
    
    public function destroy($id) {
        $this->repo->delete($id);
        return redirect()->back()->with("success", "Customer deleted successfully");
    }

    public function search(Request $request) {
        $q = $request->query('q', '');
        return response()->json($this->repo->search($q));
    }

    public function resetPin($id) {
        $customer = \App\Models\Customer::findOrFail($id);
        $customer->pin = null;
        $customer->save();

        // Log the activity
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'PIN Reset',
            'model_type' => 'Customer',
            'model_id' => $customer->id,
            'details' => json_encode(['customer_name' => $customer->name, 'customer_phone' => $customer->phone]),
            'ip_address' => request()->ip()
        ]);

        return redirect()->back()->with("success", "Customer PIN has been reset. They can set a new one on next login.");
    }
}
