<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\BarWastage;
use App\Models\HappyHour;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use DB;

class BarController extends Controller
{
    /**
     * Dashboard & Wastage Log
     */
    public function wastageIndex()
    {
        $ingredients = Ingredient::where('is_alcohol', true)->orderBy('name', 'asc')->get();
        $wastages = BarWastage::with('ingredient', 'user')->orderBy('id', 'desc')->paginate(15);
        
        // Happy Hours
        $happyHours = HappyHour::orderBy('id', 'desc')->get();

        return view('admin.bar.wastage', compact('ingredients', 'wastages', 'happyHours'));
    }

    /**
     * Store Wastage and auto-deduct stock
     */
    public function storeWastage(Request $request)
    {
        $validated = $request->validate([
            'ingredient_id' => 'required|exists:ingredients,id',
            'type' => 'required|in:Breakage,Spill,Free Pour,Complimentary',
            'measure_type' => 'required|in:bottle,peg30,peg60,peg90,custom_ml',
            'custom_ml' => 'nullable|numeric|min:1',
            'notes' => 'nullable|string',
        ]);

        $ingredient = Ingredient::findOrFail($validated['ingredient_id']);
        $bottleSize = floatval($ingredient->bottle_size_ml) ?: 750.0;

        // Calculate quantity to deduct in units (bottles)
        $volumeMl = 0.0;
        $qtyDeduct = 0.0;

        if ($validated['measure_type'] === 'bottle') {
            $qtyDeduct = 1.0;
            $volumeMl = $bottleSize;
        } elseif ($validated['measure_type'] === 'peg30') {
            $volumeMl = 30.0;
            $qtyDeduct = 30.0 / $bottleSize;
        } elseif ($validated['measure_type'] === 'peg60') {
            $volumeMl = 60.0;
            $qtyDeduct = 60.0 / $bottleSize;
        } elseif ($validated['measure_type'] === 'peg90') {
            $volumeMl = 90.0;
            $qtyDeduct = 90.0 / $bottleSize;
        } elseif ($validated['measure_type'] === 'custom_ml') {
            $volumeMl = floatval($request->input('custom_ml', 0));
            $qtyDeduct = $volumeMl / $bottleSize;
        }

        if ($qtyDeduct <= 0) {
            return redirect()->back()->with('error', 'Invalid quantity calculated.');
        }

        DB::beginTransaction();
        try {
            // Deduct stock
            $ingredient->decrement('stock_quantity', $qtyDeduct);

            // Log Wastage
            BarWastage::create([
                'tenant_id' => $ingredient->tenant_id,
                'ingredient_id' => $ingredient->id,
                'type' => $validated['type'],
                'quantity' => $qtyDeduct,
                'volume_ml' => $volumeMl,
                'logged_by' => auth()->id(),
                'notes' => $validated['notes'],
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Bar wastage logged and inventory updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error logging wastage: ' . $e->getMessage());
        }
    }

    /**
     * Excise & Consumption Reports
     */
    public function exciseReport()
    {
        $liquors = Ingredient::where('is_alcohol', true)->orderBy('name', 'asc')->get();

        // Let's compute total consumption (ml) from sales
        // We look at completed orders and their recipes
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        // Calculate consumed stock based on order recipes
        $salesConsumption = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('item_ingredients', 'order_items.item_id', '=', 'item_ingredients.item_id')
            ->join('ingredients', 'item_ingredients.ingredient_id', '=', 'ingredients.id')
            ->where('ingredients.is_alcohol', true)
            ->whereNotIn('orders.status', ['Cancelled', 'Pending Payment', 'Payment Failed'])
            ->select(
                'ingredients.id as ingredient_id',
                'ingredients.name as ingredient_name',
                'ingredients.bottle_size_ml',
                DB::raw('SUM(order_items.quantity * item_ingredients.quantity) as total_units_sold')
            )
            ->groupBy('ingredients.id', 'ingredients.name', 'ingredients.bottle_size_ml')
            ->get()
            ->keyBy('ingredient_id');

        // Let's compute wastage by type
        $wastageStats = BarWastage::select(
            'ingredient_id',
            DB::raw("SUM(CASE WHEN type = 'Breakage' THEN quantity ELSE 0 END) as total_breakage"),
            DB::raw("SUM(CASE WHEN type = 'Spill' THEN quantity ELSE 0 END) as total_spill"),
            DB::raw("SUM(CASE WHEN type = 'Free Pour' THEN quantity ELSE 0 END) as total_free_pour"),
            DB::raw("SUM(CASE WHEN type = 'Complimentary' THEN quantity ELSE 0 END) as total_complimentary")
        )
        ->groupBy('ingredient_id')
        ->get()
        ->keyBy('ingredient_id');

        return view('admin.bar.excise', compact('liquors', 'salesConsumption', 'wastageStats'));
    }

    /**
     * Happy Hours Rules store
     */
    public function storeHappyHour(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'discount_percent' => 'required|numeric|min:0|max:100',
            'start_time' => 'required',
            'end_time' => 'required',
            'days' => 'required|array',
            'days.*' => 'string',
        ]);

        HappyHour::create([
            'name' => $validated['name'],
            'discount_percent' => $validated['discount_percent'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'days_of_week' => implode(',', $validated['days']),
            'is_active' => true
        ]);

        return redirect()->back()->with('success', 'Happy hour schedule created successfully.');
    }

    public function toggleHappyHour($id)
    {
        $hh = HappyHour::findOrFail($id);
        $hh->update(['is_active' => !$hh->is_active]);

        return redirect()->back()->with('success', 'Happy Hour status toggled successfully.');
    }

    public function destroyHappyHour($id)
    {
        $hh = HappyHour::findOrFail($id);
        $hh->delete();

        return redirect()->back()->with('success', 'Happy Hour schedule deleted successfully.');
    }
}
