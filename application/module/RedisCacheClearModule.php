<?php
/**
 * Created by PhpStorm.
 * User: marin
 * Date: 2017/12/19
 * Time: 17:28
 */
namespace App\module;

use App\core\AsynRedis;
use App\core\RedisCache;
use App\worker\job\ClearCache;

class RedisCacheClearModule
{
    /** @var $_redisCache AsynRedis */
    public  $_redisCache;
    public function __construct()
    {
        $this->_redisCache = AsynRedis::Single('redis_list');
    }

    public  function testCall(\Redis $instance,$channelName,$message)
    {
        switch ($channelName){
            case ClearCache::KEY_EVENT_EXPIRED:
                $this->_redisCache->lpush($message,serialize(time()));
                break;
            default:
                break;
        }
    }

}