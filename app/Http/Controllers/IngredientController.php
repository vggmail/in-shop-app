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
        ]);

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
        ]);

        $ingredient->update($validated);

        return redirect()->back()->with('success', 'Ingredient updated successfully.');
    }

    public function destroy(Ingredient $ingredient)
    {
        $ingredient->delete();
        return redirect()->back()->with('success', 'Ingredient deleted successfully.');
    }
}
