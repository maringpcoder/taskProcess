<?php
/**
 * @description 接收订阅消息写入到redis
 * 由于订阅可能存在并发,以及环境因素导致订阅消息处理不及时或消费不过来而影响redis自身性能的缘故，所以考虑加入到redis队列中,将消息持久化
 * 后边采用多开进程来处理这部分队列
 * Created by PhpStorm.
 * User: marin
 * Date: 2017/12/19
 * Time: 17:28
 */
namespace App\module;

use App\core\RedisCache;
use App\worker\job\ClearCache;

class RedisCacheClear
{
    /** @var  $_redis \Redis */
    protected $_redis;
    protected static $_single =null;
    public static $_list_key_conf = [
        ClearCache::KEY_EVENT_EXPIRED=>'panda_user_expire'
    ];


    public function __construct()
    {
        $this->_redis = new \Redis();
        $this->_redis ->connect('192.168.1.125',6379);
    }

    /**
     * 获取单例
     */
    public static function getSingle()
    {
        if(!(self::$_single instanceof self)){
            self::$_single = new RedisCacheClear();
        }
        return self::$_single;
    }

    /**
     * @description 订阅消息处理回调,写入到redis队里中
     * @param \Redis $instance
     * @param $channelName
     * @param $message
     */
    public  function joinExpiredListHandler(\Redis $instance,$channelName,$message)
    {
        switch ($channelName){
            case ClearCache::KEY_EVENT_EXPIRED://加入到redis用户过期数据队列中,并记录何时过期的时间戳
                if(call_user_func([$this,'pushList'],self::$_list_key_conf[$channelName],$message)===false){
                    error_log(date('Y-m-d H:i:s')."\t"." $message 加入redis队列失败!".PHP_EOL,3,LOG_PATH."join_redis_err.log");
                }

                break;
            default:
                break;
        }
    }

    protected function pushList($key,$value)
    {
        $s=$this->_redis->lPush($key,$value);
        return $s;
    }
}