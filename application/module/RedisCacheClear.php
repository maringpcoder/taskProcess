<?php
/**
 * 由于订阅可能存在并发,以及环境因素导致订阅消息处理不及时或消费不过来而影响redis自身性能的缘故，所以考虑加入到redis队列中,将消息持久化
 * 后边采用多开进程来处理这部分队列
 * Created by PhpStorm.
 * User: marin
 * Date: 2017/12/19
 * Time: 17:28
 */
namespace App\module;

use App\core\AsynRedis;
use App\worker\job\ClearCache;

class RedisCacheClear
{
    /** @var $_redisCache AsynRedis */
    public  $_redisCache;
    public function __construct()
    {
        $this->_redisCache = AsynRedis::Single('redis_list');
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
            case ClearCache::KEY_EVENT_EXPIRED://加入到redis用户过期数据队列中
                $this->_redisCache->lpush($message,serialize(time()));
                break;
            default:
                break;
        }
    }

}