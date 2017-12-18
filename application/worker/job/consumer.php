<?php
/**
 * kafka消费者
 * Created by PhpStorm.
 * User: marin
 * Date: 2017/12/16
 * Time: 14:56
 */

namespace App\worker\job;
use Kafka\ConsumerConfig;
use Monolog\Handler\StdoutHandler;
use Monolog\Logger;

class Consumer
{

    /**
     * @description 消费
     */
    public static function run()
    {
        $logger = new Logger('over_time_change');
        $logger ->pushHandler(new StdoutHandler());

        $config = ConsumerConfig::getInstance();
        $config->setMetadataRefreshIntervalMs(1000);
        $config->setMetadataBrokerList('172.16.61.176:9092');
        $config->setGroupId('test');
        $config->setBrokerVersion('1.0.0');
        $config->setTopics(['test']);
        $config->setOffsetReset('earliest');
        try{
            $consumer = new \Kafka\Consumer();
            $consumer->setLogger($logger);
            $consumer->start(function ($topc, $partition, $message) {
                    error_log("consumer_message:" . json_encode($message) . PHP_EOL, 3, LOG_PATH . 'consumer.log');
            },false);

        }catch (\Exception $exception){
            error_log($exception->getMessage(),3,LOG_PATH.'exception.log');
        }
    }


}