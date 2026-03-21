<?php
namespace App\Repositories;
use App\Models\Coupon;

class CouponRepository {
    public function getAll() { return Coupon::orderBy("id", "DESC")->get(); }
    public function find($id) { return Coupon::findOrFail($id); }
    public function create($data) { return Coupon::create($data); }
    public function update($id, $data) { $c = $this->find($id); $c->update($data); return $c; }
    public function delete($id) { return $this->find($id)->delete(); }
    public function findByCode($code) { return Coupon::where("code", $code)->first(); }
}
