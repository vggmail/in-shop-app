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
        $data = $request->all();
        // If type is Internal, force show_on_home to be 0
        if(($data['coupon_type'] ?? '') == 'Internal') $data['show_on_home'] = 0;
        else $data['show_on_home'] = $request->has('show_on_home') ? 1 : 0;
        
        $this->repo->create($data);
        return redirect()->back()->with("success", "Coupon added successfully");
    }
    
    public function update(Request $request, $id) {
        $data = $request->except("_method", "_token");
        // If type is Internal, force show_on_home to be 0
        if(($data['coupon_type'] ?? '') == 'Internal') $data['show_on_home'] = 0;
        else $data['show_on_home'] = $request->has('show_on_home') ? 1 : 0;
        
        $this->repo->update($id, $data);
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
