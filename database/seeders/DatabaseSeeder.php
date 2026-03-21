<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\ItemExtra;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::create(['name' => 'Admin']);
        $cashierRole = Role::create(['name' => 'Cashier']);

        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'phone' => '1234567890',
            'role_id' => $adminRole->id,
            'password' => bcrypt('password'),
        ]);

        $cat1 = Category::create(['name' => 'Burger', 'slug' => 'burger']);
        $cat2 = Category::create(['name' => 'Pizza', 'slug' => 'pizza']);
        $cat3 = Category::create(['name' => 'Chowmein', 'slug' => 'chowmein']);

        // Burger
        $item1 = Item::create([
            'category_id' => $cat1->id,
            'name' => 'Chicken Burger',
            'price' => 5.00,
            'is_available' => true,
        ]);
        ItemExtra::create(['item_id' => $item1->id, 'name' => 'Extra Cheese', 'price' => 1.00]);
        ItemExtra::create(['item_id' => $item1->id, 'name' => 'Extra Mayo', 'price' => 0.50]);

        // Pizza
        $item2 = Item::create([
            'category_id' => $cat2->id,
            'name' => 'Pepperoni Pizza',
            'price' => 10.00,
            'is_available' => true,
        ]);
        ItemVariant::create(['item_id' => $item2->id, 'name' => 'Medium', 'price' => 10.00]);
        ItemVariant::create(['item_id' => $item2->id, 'name' => 'Large', 'price' => 15.00]);
        ItemExtra::create(['item_id' => $item2->id, 'name' => 'Mushroom Topping', 'price' => 2.00]);
    }
}
