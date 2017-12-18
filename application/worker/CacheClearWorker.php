<?php
/**
 *
 * 订阅redis key过期事件
 * Created by PhpStorm.
 * User: marin
 * Date: 2017/12/18
 * Time: 16:49
 */

namespace App\worker;


use App\core\RedisCache;
use App\lib\Config;

class CacheClearWorker
{
    protected $_worker = null;

    public function __construct(\swoole_process $worker)
    {
        $this->_worker = $worker;
        swoole_set_process_name(sprintf($this->getWorkerProcessName().':%s','worker'));
        error_log(date('Y-m-d H:i:s')."\t: The Worker Process Worker Start!".PHP_EOL,3,'cache_clear_worker.log');
        $this->workerStart();
    }

    /**
     * 开始工作
     */
    public function workerStart()
    {
       $predis =  RedisCache::getSingleRedis(true);
       
    }

    public function Start(\swoole_process $worker)
    {
        new self($worker);
    }


    /**
     * 获取子进程 ,eq (clear_master:redis_cache_clear)
     * @return string
     */
    public function getWorkerProcessName()
    {
        return sprintf(Config::getConfig('clearCache_section')->panda_process.':%s',Config::getConfig()->children->redis_cache_clear);
    }
    /**
     * 获取主进程
     */
    public function getMpProcessName()
    {
        $prefix = Config::getConfig()->panda_process .':%s';
        return sprintf($prefix,Config::getConfig()->master->log_master_name);
    }
}