<?php
/**
 *
 * 订阅redis key过期事件worker
 * Created by PhpStorm.
 * User: marin
 * Date: 2017/12/18
 * Time: 16:49
 */

namespace App\worker;
use App\ClearCacheMaster;
use App\lib\Config;
use App\worker\job\ClearCache;

class CacheClearWorker
{
    protected $_worker = null;

    public function __construct(\swoole_process $worker)
    {
        $this->_worker = $worker;
        swoole_set_process_name(sprintf($this->getWorkerProcessName().':%s','worker'));
        error_log(date('Y-m-d H:i:s')."\t: The Worker Process Worker Start!".PHP_EOL,3,'ClearCacheWork.log');
        $this->workerStart();
    }

    /**
     * 开始工作
     */
    public function workerStart()
    {
        try {
            swoole_timer_tick(3000,[$this,'checkMainProcessIFexists'],$this->_worker);
            ClearCache::run();
           }catch (\Exception $exception){
            error_log(date('Y-m-d H:i:s')."\t"."Message:{$exception->getMessage()}, 
              ClearCacheWork Quit!,ErrorCode:{$exception->getCode()}.\n",3,LOG_PATH.'ClearCacheWork.log');
            $this->_worker->exit();
        }
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
    /**
     * 检查主进程是否存在
     * @param $timerId
     * @param \swoole_process $worker
     */
    public function checkMainProcessIFexists($timerId,$worker)
    {
        $mpId = ClearCacheMaster::getMpId();
        if(!\swoole_process::kill($mpId,0)){//父进程已经不存在,退出当前worker,回收进程资源
            $worker->exit();
            error_log(date('Y-m-d H:i:s')."\t"."Message: ticket[{$timerId}] check ClearCacheWork Quit!\n",3,LOG_PATH.'ClearCacheWork.log');
        }
    }
}