<?php

require_once __DIR__ .'/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
if($dotenv->load()){
echo "Environment Variable";
}else{
    echo "Could Not Load Environment variables";
}
