<?php
include __DIR__ . '/../vendor/autoload.php';

use Slince\Spider\Spider;

$spider = new Spider();
$spider->go('http://www.baidu.com');