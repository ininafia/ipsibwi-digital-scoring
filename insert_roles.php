<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$roles = ['wasit', 'delegasi_teknik'];
foreach ($roles as $role) {
    $exists = Illuminate\Support\Facades\DB::table('roles')->where('nama', $role)->exists();
    if (!$exists) {
        Illuminate\Support\Facades\DB::table('roles')->insert(['nama' => $role]);
        echo "Inserted $role\n";
    }
}
echo "Done\n";
