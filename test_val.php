<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

Illuminate\Support\Facades\DB::enableQueryLog();

$data = ['nomor'=>3, 'partai'=>'001', 'gelanggang'=>'A', 'kelas'=>'A', 'golongan'=>'dewasa', 'jenis_kelamin'=>'putra'];
$validator = Illuminate\Support\Facades\Validator::make($data, [
    'partai' => [
        \Illuminate\Validation\Rule::unique('pertandingan', 'partai')->whereNull('deleted_at')
    ]
]);

var_dump($validator->fails());
var_dump(Illuminate\Support\Facades\DB::getQueryLog());
