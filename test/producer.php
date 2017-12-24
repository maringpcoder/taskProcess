<?php
$rk = new RdKafka\Producer();
$rk->setLogLevel(LOG_DEBUG);
$rk->addBrokers("172.16.61.176");
$topic = $rk->newTopic("test");
$topic->produce(RD_KAFKA_PARTITION_UA, 0, "Message zhengdiao ".time());