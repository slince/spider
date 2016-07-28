<?php
include __DIR__ . '/../vendor/autoload.php';

use Slince\Spider\Spider;
use Slince\Spider\Handler\FileHandler;

$spider = new Spider();
$spider->pushHandler(new FileHandler('./save/'));
$spider->run('http://www.shein.com');