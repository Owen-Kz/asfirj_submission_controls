<?php

require_once __DIR__ .'/../vendor/autoload.php';

// Adjust the path to point to the backend directory
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../backend');
$dotenv->load();
