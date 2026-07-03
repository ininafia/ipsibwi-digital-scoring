<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

Schema::table('skor_pertandingan', function (Blueprint $table) {
    if (!Schema::hasColumn('skor_pertandingan', 'jatuhan_biru')) {
        $table->integer('jatuhan_biru')->default(0);
    }
    if (!Schema::hasColumn('skor_pertandingan', 'jatuhan_merah')) {
        $table->integer('jatuhan_merah')->default(0);
    }
});
echo "Jatuhan columns added successfully.\n";
