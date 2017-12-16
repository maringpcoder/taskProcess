<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/15
 * Time: 11:26
 */

require_once 'vendor/autoload.php';


$conf = new \RdKafka\Conf();
$conf->set('group.id', 0);   //设置groupid
$conf->set('metadata.broker.list', '172.16.61.176:9092');   //设置brokerlist

//设置和topic相关参数
$topicConf = new \RdKafka\TopicConf();
$topicConf->set('auto.offset.reset', 'smallest');   //从开头消费最新消息,类似设置from-beginning
$conf->setDefaultTopicConf($topicConf);

//实例化消费者
$consumer = new \RdKafka\KafkaConsumer($conf);

//消费者订阅topic(可订阅多个)
$consumer->subscribe(['test']);

echo "wait message...\n";

while (true) {      //阻塞等待获取消息队列中的消息

    $message = $consumer->consume(120 * 1000);   //获取队列并往下执行消息,设置timeout
    var_dump($message);
//    switch ($message->err) {
//
//        case RD_KAFKA_RESP_NO_ERROR:   //当获取消息没有错误时执行处理消息操作
//            echo "message payload...";
//            //do anything you want （这里测试的话我就把消息写入文件了）
//            $msg = $message->payload;
//            error_log($msg,3,'kafka.log');
//            //file_put_contents(__DIR__ . '/kafka.log', $msg ."\n", FILE_APPEND);
//            break;
//
//    }

    sleep(1);    //休眠一秒防止服务器压力过大崩溃
}