<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Ingredient;
use App\Models\ItemIngredient;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    public function index()
    {
        $items = Item::with('category', 'ingredients.ingredient')->orderBy('name', 'asc')->get();
        return view('admin.inventory.recipes.index', compact('items'));
    }

    public function edit(Item $item)
    {
        $item->load('variants', 'ingredients.ingredient');
        $ingredients = Ingredient::orderBy('name', 'asc')->get();
        return view('admin.inventory.recipes.edit', compact('item', 'ingredients'));
    }

    public function update(Request $request, Item $item)
    {
        // Clear existing recipes for this item
        ItemIngredient::where('item_id', $item->id)->delete();

        if ($request->has('ingredients')) {
            foreach ($request->ingredients as $recipe) {
                if (!empty($recipe['ingredient_id']) && !empty($recipe['quantity'])) {
                    ItemIngredient::create([
                        'item_id' => $item->id,
                        'variant_id' => $recipe['variant_id'] ?? null,
                        'ingredient_id' => $recipe['ingredient_id'],
                        'quantity' => $recipe['quantity'],
                    ]);
                }
            }
        }

        return redirect()->route('recipes.index')->with('success', 'Recipe updated successfully.');
    }
}
