<?php
include __DIR__ . '/../vendor/autoload.php';

use Slince\Spider\Spider;
use Slince\Spider\Handler\FileHandler;

$spider = new Spider();
$spider->pushHandler(new FileHandler('./save/'));
//$spider->getDispatcher()->bind(Spider::EVENT_CAPTURED_URL, function(){
//    echo 1234;
//});
$spider->go('http://www.baidu.com');