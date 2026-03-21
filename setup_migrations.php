<?php
$dir = __DIR__ . '/database/migrations';
$files = scandir($dir);

// Rename roles migration so it runs before users
foreach ($files as $file) {
    if (strpos($file, 'create_roles_table') !== false) {
        rename($dir . '/' . $file, $dir . '/2013_01_01_000000_create_roles_table.php');
        break;
    }
}

// Rename categories to avoid conflicts with products foreign key if they run in wrong order
foreach ($files as $file) {
    if (strpos($file, 'create_categories_table') !== false) {
        rename($dir . '/' . $file, $dir . '/2014_01_01_000000_create_categories_table.php');
        break;
    }
}

// Reload files
$files = scandir($dir);

foreach ($files as $file) {
    if ($file === '.' || $file === '..') continue;
    $path = $dir . '/' . $file;
    $content = file_get_contents($path);

    if (strpos($file, 'create_users_table') !== false) {
        $content = str_replace(
            "\$table->string('email')->unique();",
            "\$table->string('email')->unique();\n            \$table->foreignId('role_id')->nullable()->constrained()->onDelete('set null');\n            \$table->string('phone')->nullable();",
            $content
        );
    } elseif (strpos($file, 'create_roles_table') !== false) {
        $content = preg_replace(
            "/\\\$table->id\(\);/",
            "\$table->id();\n            \$table->string('name');",
            $content
        );
    } elseif (strpos($file, 'create_categories_table') !== false) {
        $content = preg_replace(
            "/\\\$table->id\(\);/",
            "\$table->id();\n            \$table->string('name');\n            \$table->string('slug')->unique();",
            $content
        );
    } elseif (strpos($file, 'create_products_table') !== false) {
        $content = preg_replace(
            "/\\\$table->id\(\);/",
            "\$table->id();\n            \$table->foreignId('category_id')->constrained()->cascadeOnDelete();\n            \$table->string('name');\n            \$table->string('sku')->unique();\n            \$table->decimal('price', 10, 2);\n            \$table->integer('stock_quantity');\n            \$table->integer('low_stock_alert')->default(5);",
            $content
        );
    } elseif (strpos($file, 'create_customers_table') !== false) {
        $content = preg_replace(
            "/\\\$table->id\(\);/",
            "\$table->id();\n            \$table->string('name');\n            \$table->string('phone')->unique();\n            \$table->string('email')->nullable();\n            \$table->integer('total_orders')->default(0);\n            \$table->decimal('total_purchase', 10, 2)->default(0);",
            $content
        );
    } elseif (strpos($file, 'create_coupons_table') !== false) {
        $content = preg_replace(
            "/\\\$table->id\(\);/",
            "\$table->id();\n            \$table->string('code')->unique();\n            \$table->string('discount_type'); // fixed, percentage\n            \$table->decimal('value', 10, 2);\n            \$table->decimal('min_order_amount', 10, 2)->nullable();\n            \$table->date('expiry_date')->nullable();",
            $content
        );
    } elseif (strpos($file, 'create_orders_table') !== false) {
        $content = preg_replace(
            "/\\\$table->id\(\);/",
            "\$table->id();\n            \$table->string('order_number')->unique();\n            \$table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();\n            \$table->decimal('total_amount', 10, 2);\n            \$table->decimal('discount_amount', 10, 2)->default(0);\n            \$table->decimal('grand_total', 10, 2);\n            \$table->string('payment_method'); // Cash, UPI, Card\n            \$table->string('payment_status'); // Paid, Pending\n            \$table->string('order_status')->default('Completed');",
            $content
        );
    } elseif (strpos($file, 'create_order_items_table') !== false) {
        $content = preg_replace(
            "/\\\$table->id\(\);/",
            "\$table->id();\n            \$table->foreignId('order_id')->constrained()->cascadeOnDelete();\n            \$table->foreignId('product_id')->constrained()->cascadeOnDelete();\n            \$table->integer('quantity');\n            \$table->decimal('price', 10, 2);\n            \$table->decimal('total', 10, 2);",
            $content
        );
    } elseif (strpos($file, 'create_payments_table') !== false) {
        $content = preg_replace(
            "/\\\$table->id\(\);/",
            "\$table->id();\n            \$table->foreignId('order_id')->constrained()->cascadeOnDelete();\n            \$table->string('payment_method');\n            \$table->decimal('paid_amount', 10, 2);\n            \$table->date('payment_date')->nullable();\n            \$table->string('payment_status');",
            $content
        );
    } elseif (strpos($file, 'create_coupon_usages_table') !== false) {
        $content = preg_replace(
            "/\\\$table->id\(\);/",
            "\$table->id();\n            \$table->foreignId('coupon_id')->constrained()->cascadeOnDelete();\n            \$table->foreignId('order_id')->constrained()->cascadeOnDelete();\n            \$table->decimal('discount_amount', 10, 2);",
            $content
        );
    }
    file_put_contents($path, $content);
}
echo "Migrations Updated\n";
