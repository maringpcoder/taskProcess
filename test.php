<?php

$redis = new swoole_redis();
$redis->connect('172.16.61.100', 6379, function($c,$r){});

try {
    $combile = $redis->connect('172.16.61.100', 6379, function($c,$r){});

}catch (Exception $e){
    echo $e->getMessage();
}
//var_dump($combile);


function lpuuu(swoole_redis $redisClient, $result)
{
    try {
        var_dump($result);
        if ($result!==false) {
//            $result && $redisClient->lpush('my_list', time(), function (swoole_redis $client, $res) {
//                return [$res];
//            });
            $redisClient->set('key','swool',function($client,$result){});
//    var_dump($result);
//    $redisClient->set();
        } else {
            var_dump($result);
            throw new Exception($result);
//            throw new Exception($redisClient->errCode);
        }
    }catch (\Exception $exception){
        echo var_dump($exception->getMessage());
        //throw new Exception($exception->getMessage());
    }

}
die();





//ini_set('default_socket_timeout', -1);  //不超时
//try{
//    $redis = new Redis();
//    $redis->pconnect('172.16.61.100', 6379);
//    $redis->subscribe(array('__keyevent@0__:expired'),'tell');
//}catch (RedisException $e){
//    echo 'Error:'.$e->getMessage()."\r\n";
//}
////$result=$redis->subscribe(array('中央广播电台'), 'tell');
//
//function tell(Redis $instance,$channelName,$message){
//    //error_log(33333,3,'3.log');
//    //var_dump($instance);
//    $timeNow= microtime(true);
////    echo $timeNow."..... ".$message."    ....message:$channelName  \r\n";
//    call_user_func('lPushList',$message.':'.$timeNow);
//   // $instance->lPush('list_over_time',$message.':'.$timeNow);
//}
//
//function lPushList($listVal)
//{
//    static  $redis = null;
//    if(!$redis){
//        $redis = new Redis();
//        $redis ->connect('172.16.61.100',6379);
//    }
//    $redis ->lPush('over_key_list',$listVal);
//}
