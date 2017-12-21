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
use App\server\PandaTaskServer;
use App\worker\job\ClearCache;

class kvExpiredHandler
{
    protected $_redis;
    protected $_arCache;
    protected $_worker;
    public function __construct(\swoole_process $worker)
    {
        $this->_worker = $worker;
        $this->_redis = RedisCache::getSingleRedis(false,'redis_list');
        $this->_arCache = RedisCache::getSingleRedis(false,'redis_kv_expire');
    }

    public function run()
    {
        while (1) {
            swoole_timer_tick(10000, [$this, 'checkMainProcessIFexists'], $this->_worker);
            $data = $this->_redis->rpop(RedisCacheClear::$_list_key_conf[ClearCache::KEY_EVENT_EXPIRED]);
            $this->deleteExpireField($data);
        }
    }

    public function deleteExpireField($data)
    {
        list($uid,$nid) = each($data);
        foreach (AppConf::EXPIRE_KEY as $value) {
            $this->_arCache->hDel($value,$uid);
        }
    }

    /**
     * 检查主进程是否存在
     * @param $timerId
     * @param \swoole_process $worker
     */
    public function checkMainProcessIFexists($timerId,$worker)
    {
        $mpId = PandaTaskServer::getMpId();
        error_log('time:'.time().PHP_EOL,3,LOG_PATH.'PandaTaskServer.log');
        if(!\swoole_process::kill($mpId,0)){//父进程已经不存在,退出当前worker,回收进程资源
            error_log(date('Y-m-d H:i:s')."\t"."Message: ticket[{$timerId}] check PandaTaskServer Quit!",3,LOG_PATH.'PandaTaskServer.log');
            $worker->exit();
            swoole_timer_clear($timerId);
        }
    }
}