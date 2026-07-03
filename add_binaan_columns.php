<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

Schema::table('skor_pertandingan', function (Blueprint $table) {
    if (!Schema::hasColumn('skor_pertandingan', 'binaan_biru')) {
        $table->integer('binaan_biru')->default(0);
    }
    if (!Schema::hasColumn('skor_pertandingan', 'binaan_merah')) {
        $table->integer('binaan_merah')->default(0);
    }
});
echo "Columns added successfully.\n";
