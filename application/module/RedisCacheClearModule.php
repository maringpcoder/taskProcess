<?php
/**
 * Created by PhpStorm.
 * User: marin
 * Date: 2017/12/19
 * Time: 17:28
 */
namespace App\module;

use App\core\RedisCache;

class RedisCacheClearModule
{
    static  $_redisCache;
    public function __construct()
    {

    }

    public  function testCall(\Redis $instance,$channelName,$message)
    {

    }

}