<?php
/**
 * @description 任务栈
 * Created by PhpStorm.
 * User: marin
 * Date: 2017/12/21
 * Time: 14:28
 */
namespace App\server;


use App\lib\Config;

class PandaTaskServer
{
    protected $_config=[];
    protected $_pandaServer=null;
    protected $_processWorker = [];
    protected $_maxProcessSize;
    protected $_lock;
    protected static $_mpId=null;

    public function __construct()
    {
        $this->_config = Config::getConfigArr('panda_server_section');
        swoole_set_process_name($this->getConsumerMp());
        $this->_lock = new \swoole_lock(SWOOLE_MUTEX);
        self::$_mpId = posix_getpid();
        \swoole_process::signal(SIGCHLD,[$this,'waitExit']);
    }

    public function waitExit()
    {
        while($process=\swoole_process::wait(false)){
            $this->_lock->lock();
            if(isset($this->_processWorker[strval($process['pid'])])){
                unset($this->_processWorker[strval($process['pid'])]);
            }
            $this->_lock->unlock();
        }
        $this->Start();
    }

    public static function getMpId()
    {
        return self::$_mpId;
    }

    /**
     * 启动
     */
    public  function Start()
    {
        $this->kvExpiredHandler();
    }

    /**
     * 取出队列数据,处理过期数据
     */
    protected function kvExpiredHandler()
    {
        $this->_lock ->lock();
        try{
            $currentWorker = $this->_maxProcessSize - count($this->_processWorker);
            for($n=0;$n<$currentWorker;$n++){
                $process = new \swoole_process(['\\App\\module\\kvExpiredHandler','run']);
                $processId = $process->start();
                $this->_processWorker[$processId] = microtime(true);
                usleep(200);

            }
        }catch (\Exception $exception){
            //todo kill reboot?

        }
        $this->_lock->unlock();
    }


    protected function getConsumerMp()
    {
        $prefix = $this->getMpNamePrefix().':%s';
        return sprintf($prefix,$this->_config['panda_process_master']);
    }


    /**
     * 获取主进程前缀名
     */
    public  function getMpNamePrefix()
    {
        return $this->_config['panda_process'];
    }
}