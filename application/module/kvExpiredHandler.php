<?php
/**
 * Created by PhpStorm.
 * User: marin
 * Date: 2017/12/21
 * Time: 16:14
 */

namespace App\module;

use App\config\AppConf;
use App\core\RedisCache;
use App\worker\job\ClearCache;

class kvExpiredHandler
{
    protected $_redis;
    protected $_arCache;
    public function __construct()
    {
        $this->_redis = RedisCache::getSingleRedis(false,'redis_list');
        $this->_arCache = RedisCache::getSingleRedis(false,'redis_kv_expire');
    }

    public function run()
    {

        $data=$this->_redis->rpop(RedisCacheClear::$_list_key_conf[ClearCache::KEY_EVENT_EXPIRED]);
        $this->deleteExpireField($data);
    }

    public function deleteExpireField($data)
    {
        list($uid,$nid) = each($data);
        foreach (AppConf::EXPIRE_KEY as $value) {
            $this->_arCache->hDel($value,$uid);
        }
    }

}