<?php
/**
 * kafka消费者
 * Created by PhpStorm.
 * User: marin
 * Date: 2017/12/16
 * Time: 14:56
 */

namespace App\worker\job;
use App\config\AppConf;
use App\lib\Config;
use App\worker\Worker;
use Kafka\ConsumerConfig;
use Monolog\Handler\StdoutHandler;
use Monolog\Logger;
use RdKafka\KafkaConsumer;

class Consumer
{

    /**
     * @description 消费
     * @param $consumerWorkerProcess Worker
     */
    public static function run($consumerWorkerProcess)
    {
        static $brokerList=null;
        if($brokerList==null){
            $config = Config::getConfigArr('log_section');
            $brokerList = $config['broker_list'];
        }
        $logger = new Logger('over_time_change');
        $logger ->pushHandler(new StdoutHandler());

        $config = ConsumerConfig::getInstance();
        $config->setMetadataRefreshIntervalMs(AppConf::$kafkaConsumerAppConfig['RefreshIntervalMs']);
        $config->setMetadataBrokerList($brokerList);
        $config->setGroupId(AppConf::$kafkaConsumerAppConfig['GroupId']);
        $config->setBrokerVersion(AppConf::$kafkaConsumerAppConfig['BrokerVersion']);
        $config->setTopics(AppConf::$kafkaConsumerAppConfig['Topics']);
        $config->setOffsetReset(AppConf::$kafkaConsumerAppConfig['OffsetReset']);
        try{
            $consumer = new \Kafka\Consumer();
            //$consumer->setLogger($logger);
            $consumer->start(function ($topc, $partition, $message)use ($consumerWorkerProcess) {
                error_log("consumer_message:" . json_encode($message) . PHP_EOL, 3, LOG_PATH . 'consumer.log');
                $consumerWorkerProcess->checkMasterProcessExists();
            });
        }catch (\Exception $exception){

            error_log($exception->getMessage(),3,LOG_PATH.'exception.log');
        }
    }


}