<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$sql = file_get_contents(__DIR__.'/database/digital-scoring.sql');
\Illuminate\Support\Facades\DB::unprepared($sql);
echo "Database imported successfully.\n";
