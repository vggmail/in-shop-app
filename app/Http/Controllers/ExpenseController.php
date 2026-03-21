<?php
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
    
    public function update(Request $request, $id) {
        $this->repo->update($id, $request->all());
        return redirect()->back()->with("success", "Expense updated successfully");
    }
    
    public function destroy($id) {
        $this->repo->delete($id);
        return redirect()->back()->with("success", "Expense deleted successfully");
    }
}
