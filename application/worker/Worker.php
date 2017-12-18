<?php
/**
 * 处理kafka消息队列的进程
 * Created by PhpStorm.
 * User: marin
 * Date: 2017/12/16
 * Time: 14:02
 */
namespace App\worker;
use App\lib\Config;
use App\MasterProcess;
use App\worker\job\Consumer;

class Worker
{
    protected $_worker = null;

    public function __construct(\swoole_process $worker)
    {
        $this->_worker = $worker;
        swoole_set_process_name(sprintf($this->getWorkerProcessName().':%s','worker'));
        error_log(date('Y-m-d H:i:s')."\t: The Worker Process Worker Start!".PHP_EOL,3,LOG_PATH.'loop_worker_start.log');
        $this->loopWorker();
    }
    /**
     * 工作进程开始
     */
    public function loopWorker()
    {
        try {
            while (true) {
                Consumer::run();
                usleep(50000);
                $this->checkMasterProcessExists();
            }
        }catch (\Exception $exception){
            $this->_worker->exit();//如有异常,worker退出,等待父进程重启子进程
        }
    }

    public function Start(\swoole_process $worker)
    {
       new self($worker);
    }

    /**
     * 检查主进程是否还在,如果主进程不存在,那么主进程退出
     *
     */
    public function checkMasterProcessExists()
    {
        $mpId = MasterProcess::getMpId();
        if(!\swoole_process::kill($mpId,0)){
            $childProcessName = $this->getWorkerProcessName();
            $worker_params = json_encode($this->_worker);
            error_log(date('Y-m-d H:i:s')."\t".$this->getMpProcessName()." exited ! , {$childProcessName} [ {$worker_params} ,{$mpId}] also quit! ".PHP_EOL,3,LOG_PATH.'loop.log');
            $this->_worker ->exit();//主进程已经不存在,则退出子进程应该退出,回收资源
        }
    }

    /**
     * 获取子进程名 ,eq:panda_process:log-kafka
     * @return string
     */
    public function getWorkerProcessName()
    {
        return sprintf(Config::getConfig()->panda_process.':%s',Config::getConfig()->children->log_kafka);
    }

    /**
     * 获取主进程名
     */
    public function getMpProcessName()
    {
        $prefix = Config::getConfig()->panda_process .':%s';
        return sprintf($prefix,Config::getConfig()->master->log_master_name);
    }
}