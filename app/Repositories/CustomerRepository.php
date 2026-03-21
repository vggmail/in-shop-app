<?php
namespace App\Repositories;
use App\Models\Customer;

class CustomerRepository {
    public function getAll() { return Customer::orderBy("id", "DESC")->get(); }
    public function find($id) { return Customer::findOrFail($id); }
    public function create($data) { return Customer::create($data); }
    public function update($id, $data) { $c = $this->find($id); $c->update($data); return $c; }
    public function delete($id) { return $this->find($id)->delete(); }
    public function search($query) { return Customer::where("name", "like", "%$query%")->orWhere("phone", "like", "%$query%")->get(); }
}
