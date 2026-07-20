<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = new \Illuminate\Http\Request();
$request->merge([
    'id_pertandingan' => 2,
    'id_babak' => 1,
    'sudut' => 'merah',
    'id_kategori_nilai' => 2,
    'nilai' => 2
]);

session(['user_id' => 13, 'role' => 5, 'juri_position' => 'juri_1']);
$usecase = app(\App\Http\Usecases\JuriUsecase::class);
$result = $usecase->inputScore($request);
print_r($result);
