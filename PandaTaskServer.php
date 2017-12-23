<?php
/**
 * @description 任务栈
 * Created by PhpStorm.
 * User: marin
 * Date: 2017/12/21
 * Time: 14:28
 */
namespace App;

include_once('./application/bootstrap.php');
use App\lib\Config;
use App\module\kvExpiredHandler;

class PandaTaskServer
{
    protected $_config=[];
    protected $_pandaServer=null;
    protected $_processWorker = [];
    protected $_maxProcessSize;
    protected $_lock;
    protected static $_mpId=null;
    protected $_worker;

    public function __construct()
    {
        $this->_config = Config::getConfigArr('panda_server_section');
        $this->_maxProcessSize = $this->_config['max_process_num'];
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
    public   function Start()
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
                $kvExpiredHandlerObj = new kvExpiredHandler();
                $process = new \swoole_process([$kvExpiredHandlerObj,'run']);
                $processId = $process->start();
                $this->_processWorker[$processId] = microtime(true);
                usleep(200);

            }
        }catch (\Exception $exception){
            //todo kill reboot?

        }
        $this->_lock->unlock();
    }


    public function getConsumerMp()
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
$theServer = new PandaTaskServer();
$theServer ->Start();