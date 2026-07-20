<?php
$logFile = __DIR__ . '/storage/logs/laravel.log';
$lines = file($logFile);
$errors = [];
foreach ($lines as $line) {
    if (strpos($line, 'ERROR') !== false || strpos($line, 'Exception') !== false) {
        $errors[] = trim($line);
    }
}
$lastErrors = array_slice($errors, -20);
echo implode("\n", $lastErrors);
