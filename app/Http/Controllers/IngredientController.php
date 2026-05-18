<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    public function index()
    {
        $ingredients = Ingredient::orderBy('name', 'asc')->get();
        return view('admin.inventory.ingredients.index', compact('ingredients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|string',
            'stock_quantity' => 'required|numeric|min:0',
            'min_stock_level' => 'required|numeric|min:0',
            'cost_per_unit' => 'required|numeric|min:0',
            'is_alcohol' => 'nullable|boolean',
            'bottle_size_ml' => 'nullable|numeric|min:0',
        ]);

        $validated['is_alcohol'] = $request->boolean('is_alcohol');
        if (!$validated['is_alcohol']) {
            $validated['bottle_size_ml'] = null;
        }

        Ingredient::create($validated);

        return redirect()->back()->with('success', 'Ingredient added successfully.');
    }

    public function update(Request $request, Ingredient $ingredient)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|string',
            'stock_quantity' => 'required|numeric|min:0',
            'min_stock_level' => 'required|numeric|min:0',
            'cost_per_unit' => 'required|numeric|min:0',
            'is_alcohol' => 'nullable|boolean',
            'bottle_size_ml' => 'nullable|numeric|min:0',
        ]);

        $validated['is_alcohol'] = $request->boolean('is_alcohol');
        if (!$validated['is_alcohol']) {
            $validated['bottle_size_ml'] = null;
        }

        $ingredient->update($validated);

        return redirect()->back()->with('success', 'Ingredient updated successfully.');
    }

    public function destroy(Ingredient $ingredient)
    {
        $ingredient->delete();
        return redirect()->back()->with('success', 'Ingredient deleted successfully.');
    }
}
