<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/15
 * Time: 13:17
 */
include_once("vendor/autoload.php");
date_default_timezone_set('PRC');

use Monolog\Logger;
use Monolog\Handler\StdoutHandler;

$logger = new Logger('integral_list_change');
$logger->pushHandler(new StdoutHandler());

$config = \Kafka\ConsumerConfig::getInstance();
$config->setMetadataRefreshIntervalMs(10000);
$config->setMetadataBrokerList('172.16.61.176:9092');
$config->setGroupId('test');
$config->setBrokerVersion('1.0.0');
$config->setTopics(['test']);
$config->setOffsetReset('earliest');//latest,earliest

try {
    $consumer = new \Kafka\Consumer();
    $consumer->setLogger($logger);
    $consumer->start(function ($topic, $part, $message) {
        error_log("msg:".json_encode($message).PHP_EOL,3,'message.log');
    });
}catch (Exception $exception){
    error_log($exception->getMessage(),3,'exception.log');
}