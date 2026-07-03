<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

Schema::table('skor_pertandingan', function (Blueprint $table) {
    if (!Schema::hasColumn('skor_pertandingan', 'teguran_biru')) {
        $table->integer('teguran_biru')->default(0);
    }
    if (!Schema::hasColumn('skor_pertandingan', 'teguran_merah')) {
        $table->integer('teguran_merah')->default(0);
    }
    if (!Schema::hasColumn('skor_pertandingan', 'peringatan_biru')) {
        $table->integer('peringatan_biru')->default(0);
    }
    if (!Schema::hasColumn('skor_pertandingan', 'peringatan_merah')) {
        $table->integer('peringatan_merah')->default(0);
    }
});
echo "Hukuman columns added successfully.\n";
