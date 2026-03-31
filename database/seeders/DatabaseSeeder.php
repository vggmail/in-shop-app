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
        // 1. Seed Tenant dynamically based on the current database connection
        if (\Illuminate\Support\Facades\DB::connection('tenant')->table('tenants')->count() === 0) {
            $dbName = \Illuminate\Support\Facades\DB::connection()->getDatabaseName();
            $prefix = env('DB_PREFIX', '');
            
            // Extract subdomain from the database name
            $subdomain = str_replace($prefix, '', $dbName);
            
            // Fallback for local main database migrations
            if (empty($subdomain) || $subdomain === 'forge' || $subdomain === env('DB_DATABASE')) {
                $subdomain = 'retail'; 
            }

            \Illuminate\Support\Facades\DB::connection('tenant')->table('tenants')->insert([
                'subdomain' => $subdomain,
                'name' => ucfirst($subdomain) . ' Store',
                'is_active' => true,
                'tagline' => 'Fresh & Fast!',
                'phone' => '1234567890',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Roles & Admin
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $cashierRole = Role::firstOrCreate(['name' => 'Cashier']);

        if (User::count() === 0) {
            User::create([
                'name' => 'Admin User',
                'email' => 'admin@admin.com',
                'phone' => '1234567890',
                'role_id' => $adminRole->id,
                'password' => bcrypt('password'),
            ]);
        }

    }
}
