<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Repositories\CouponRepository;

class CouponController extends Controller {
    protected $repo;
    public function __construct(CouponRepository $repo) { $this->repo = $repo; }
    
    public function index() { 
        $coupons = $this->repo->getAll(); 
        return view("admin.coupons.index", compact("coupons")); 
    }
    
    public function store(Request $request) {
        $this->repo->create($request->all());
        return redirect()->back()->with("success", "Coupon added successfully");
    }
    
    public function update(Request $request, $id) {
        $this->repo->update($id, $request->except("_method", "_token"));
        return redirect()->back()->with("success", "Coupon updated successfully");
    }
    
    public function destroy($id) {
        $this->repo->delete($id);
        return redirect()->back()->with("success", "Coupon deleted successfully");
    }
    
    public function check(Request $request) {
        $coupon = $this->repo->findByCode($request->code);
        if($coupon) {
            return response()->json(["status" => true, "coupon" => $coupon]);
        }
        return response()->json(["status" => false, "msg" => "Invalid Coupon"]);
    }
}
