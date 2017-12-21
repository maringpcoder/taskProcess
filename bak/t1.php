<?php
/**
 * Created by PhpStorm.
 * User: marin
 * Date: 2017/12/20
 * Time: 14:07
 */
//$redis = new swoole_redis();
////echo 3333;
//$redis->connect('172.16.61.100', 6379, function($c,$r){
////    var_dump($r);
//    $c->set("keyer","easyhong",function ($client,$result){
////        var_dump($client,$result);
//        $client->close();
//    });
//});

namespace App;
use App\core\AsynRedis;
use App\server\PandaTaskServer;

include_once('./application/bootstrap.php');
//$AsynRedis = AsynRedis::Single('redis_list');
//$AsynRedis ->lpush("my_task",time());

$pandaTaskServer = new PandaTaskServer();
$pandaTaskServer->Start();
