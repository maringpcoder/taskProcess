<?php
/**
 * the client for connect server of clear cache
 * Created by PhpStorm.
 * User: marin
 * Date: 2017/12/18
 * Time: 17:16
 */

namespace App\worker\job;


use App\core\RedisCache;

class ClearCache
{

    CONST KEY_EVENT_EXPIRED = '__keyevent@0__:expired';
    /**
     * 开始工作
     */
    public static function run()
    {
        try {
            $pRedis = RedisCache::getSingleRedis(true);
            $pRedis ->subscribe([self::KEY_EVENT_EXPIRED],['App\module\RedisCacheClear','joinExpiredListHandler']);
        }catch (\Exception $exception){
            throw new \Exception($exception->getMessage(),$exception->getCode());
        }
    }

}