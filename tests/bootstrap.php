<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Load Composer autoloader when available (not required for raw mysqli tests)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
}
