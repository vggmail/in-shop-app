<?php
$dir = __DIR__ . '/database/migrations';
foreach(scandir($dir) as $f) {
    if(strpos($f, 'create_roles_table') !== false && strpos($f, '2013') === false) {
        rename($dir.'/'.$f, $dir.'/2013_01_01_000000_create_roles_table.php');
    }
    if(strpos($f, 'create_categories_table') !== false && strpos($f, '2014') === false) {
        rename($dir.'/'.$f, $dir.'/2014_01_01_000000_create_categories_table.php');
    }
}
echo "Renamed.\n";
