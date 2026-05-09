<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Category;
use App\Models\Customer;

class ExpressPosController extends Controller
{
    public function index()
    {
        $categories = Category::with(['items' => function($q) {
            $q->where('stock_quantity', '>', 0)
              ->with(['variants', 'extras'])
              ->orderBy('id', 'desc');
        }])->orderBy('name', 'asc')->get();

        $items = Item::where('stock_quantity', '>', 0)->with(['variants', 'extras', 'category'])->get();
        $topItems = $items->sortByDesc('id')->take(16)->values();

        $customers = Customer::orderBy('name', 'asc')->get();
        
        $tenant = app()->bound('tenant') ? app('tenant') : \App\Models\Tenant::first();

        return view('admin.pos.express', compact('categories', 'customers', 'tenant', 'items', 'topItems'));
    }
}
