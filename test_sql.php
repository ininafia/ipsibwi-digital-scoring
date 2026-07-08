<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    \DB::unprepared(file_get_contents('database/digital-scoring.sql'));
    echo 'SUCCESS';
} catch (Exception $e) {
    echo $e->getMessage();
}
