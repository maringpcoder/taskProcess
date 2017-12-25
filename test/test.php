<?php

ini_set('default_socket_timeout', -1);  //不超时
try{
    $redis = new Redis();
    $redis->pconnect('192.168.1.125', 6379);
    $redis->setOption(Redis::OPT_READ_TIMEOUT,-1);
    $redis->subscribe(array('__keyevent@0__:expired'),'tell');
}catch (RedisException $e){
    echo 'Error:'.$e->getMessage()."\r\n";
}
//$result=$redis->subscribe(array('中央广播电台'), 'tell');

function tell(Redis $instance,$channelName,$message){
    //error_log(33333,3,'3.log');
    //var_dump($instance);
    $timeNow= microtime(true);
    echo $timeNow;
//    echo $timeNow."..... ".$message."    ....message:$channelName  \r\n";
    //call_user_func('lPushList',$message.':'.$timeNow);
   // $instance->lPush('list_over_time',$message.':'.$timeNow);
}

function lPushList($listVal)
{
    static  $redis = null;
    if(!$redis){
        $redis = new swoole_redis();
        $redis ->connect('172.16.61.100',6379,function ($client,$rs)use ($listVal){
            $client ->lPush('over_key_list',$listVal);
        });
    }

}
