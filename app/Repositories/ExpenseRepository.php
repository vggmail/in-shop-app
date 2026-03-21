<?php
namespace App\Repositories;
use App\Models\Expense;

class ExpenseRepository {
    public function getAll() { return Expense::orderBy("date", "DESC")->get(); }
    public function create($data) { return Expense::create($data); }
    public function update($id, $data) { $e = Expense::findOrFail($id); $e->update($data); return $e; }
    public function delete($id) { return Expense::findOrFail($id)->delete(); }
}
