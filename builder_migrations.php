<?php
$dir = __DIR__ . '/database/migrations';
$files = scandir($dir);

foreach ($files as $f) {
    if (strpos($f, 'create_') !== false && strpos($f, 'users') === false && strpos($f, 'password') === false && strpos($f, 'failed_jobs') === false && strpos($f, 'personal_access_tokens') === false) {
        if(is_file("$dir/$f")) unlink("$dir/$f");
    }
}

$time = time();
function createMigration($name, $schema) {
    global $dir, $time;
    $date = date('Y_m_d_His', $time);
    if($name == 'roles') $date = '2013_01_01_000000';
    if($name == 'categories') $date = '2014_01_01_000000';
    $time++;
    $className = "return new class extends \Illuminate\Database\Migrations\Migration";
    $content = "<?php\n\nuse Illuminate\Database\Migrations\Migration;\nuse Illuminate\Database\Schema\Blueprint;\nuse Illuminate\Support\Facades\Schema;\n\n$className\n{\n    public function up()\n    {\n        Schema::create('$name', function (Blueprint \$table) {\n$schema\n        });\n    }\n\n    public function down()\n    {\n        Schema::dropIfExists('$name');\n    }\n};\n";
    file_put_contents("$dir/{$date}_create_{$name}_table.php", $content);
}

// using double quotes so \n is parsed correctly
createMigration('roles', "            \$table->id();\n            \$table->string('name');\n            \$table->timestamps();");
createMigration('categories', "            \$table->id();\n            \$table->string('name');\n            \$table->string('slug')->unique();\n            \$table->timestamps();");
createMigration('items', "            \$table->id();\n            \$table->foreignId('category_id')->constrained()->onDelete('cascade');\n            \$table->string('name');\n            \$table->string('image')->nullable();\n            \$table->decimal('price', 10, 2);\n            \$table->boolean('is_available')->default(true);\n            \$table->timestamps();");
createMigration('item_variants', "            \$table->id();\n            \$table->foreignId('item_id')->constrained()->onDelete('cascade');\n            \$table->string('name');\n            \$table->decimal('price', 10, 2);\n            \$table->timestamps();");
createMigration('item_extras', "            \$table->id();\n            \$table->foreignId('item_id')->constrained()->onDelete('cascade');\n            \$table->string('name');\n            \$table->decimal('price', 10, 2);\n            \$table->timestamps();");
createMigration('customers', "            \$table->id();\n            \$table->string('name')->nullable();\n            \$table->string('phone')->unique();\n            \$table->integer('total_orders')->default(0);\n            \$table->decimal('total_spending', 10, 2)->default(0);\n            \$table->timestamps();");
createMigration('coupons', "            \$table->id();\n            \$table->string('code')->unique();\n            \$table->decimal('discount_percentage', 5, 2);\n            \$table->date('expiry_date')->nullable();\n            \$table->decimal('min_bill_amount', 10, 2)->default(0);\n            \$table->timestamps();");
createMigration('expenses', "            \$table->id();\n            \$table->string('category');\n            \$table->decimal('amount', 10, 2);\n            \$table->date('date');\n            \$table->text('description')->nullable();\n            \$table->timestamps();");
createMigration('orders', "            \$table->id();\n            \$table->string('order_number')->unique();\n            \$table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');\n            \$table->string('order_type');\n            \$table->string('table_number')->nullable();\n            \$table->decimal('total_amount', 10, 2);\n            \$table->decimal('discount_amount', 10, 2)->default(0);\n            \$table->decimal('grand_total', 10, 2);\n            \$table->string('payment_method');\n            \$table->string('payment_status')->default('Paid');\n            \$table->string('status')->default('Preparing');\n            \$table->text('note')->nullable();\n            \$table->timestamps();");
createMigration('order_items', "            \$table->id();\n            \$table->foreignId('order_id')->constrained()->onDelete('cascade');\n            \$table->foreignId('item_id')->constrained()->onDelete('cascade');\n            \$table->foreignId('item_variant_id')->nullable()->constrained()->onDelete('set null');\n            \$table->decimal('price', 10, 2);\n            \$table->integer('quantity');\n            \$table->decimal('total', 10, 2);\n            \$table->timestamps();");
createMigration('order_item_extras', "            \$table->id();\n            \$table->foreignId('order_item_id')->constrained()->onDelete('cascade');\n            \$table->foreignId('item_extra_id')->constrained()->onDelete('cascade');\n            \$table->decimal('price', 10, 2);\n            \$table->timestamps();");
createMigration('payments', "            \$table->id();\n            \$table->foreignId('order_id')->constrained()->onDelete('cascade');\n            \$table->string('method');\n            \$table->decimal('amount', 10, 2);\n            \$table->string('status');\n            \$table->date('date');\n            \$table->timestamps();");

echo "Migrations Rewritten.\n";
