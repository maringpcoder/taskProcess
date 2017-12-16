<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/14
 * Time: 18:10
 */
$rk = new RdKafka\Consumer();
$rk->setLogLevel(LOG_DEBUG);
$rk->addBrokers("172.16.61.176");
$topic = $rk->newTopic("test");
$topic->consumeStart(0, RD_KAFKA_OFFSET_BEGINNING);
while (true) {
    // The first argument is the partition (again).
    // The second argument is the timeout.
    $msg = $topic->consume(0, 1000);
    var_dump($msg);
    if ($msg->err) {
        echo $msg->errstr(), ":error\n";
    } else {
        echo $msg->payload, "\n";
    }
}