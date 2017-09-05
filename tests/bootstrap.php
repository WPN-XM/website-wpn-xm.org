<?php

// Error Reporting Level
error_reporting(E_ALL);

// Composer Autoloader
if (is_file(__DIR__ . '/../vendor/autoload.php')) {
    include_once __DIR__ . '/../vendor/autoload.php';
} else {
    echo 'Could not find "vendor/autoload.php". Did you forget to run "composer install --dev"?' . PHP_EOL;
    exit(1);
}