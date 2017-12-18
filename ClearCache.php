<?php
/**
 * 清空缓存
 */
namespace App;
use App\lib\Config;
include_once('./application/bootstrap.php');

class ClearCache
{
    protected $_max_worker_num = 0;
    protected $_lock = null;
    protected static $_mpid = 0;//主进程id号
    protected $_workers = [];//保存worker进程

    protected $_after = null;

    public function __construct()
    {
        swoole_set_process_name($this->getConsumerMp());
        $this->_max_worker_num = Config::getConfig('clearCache_section')->cache_clear_handler->worker_num;
        self::$_mpid = posix_getpid();
        //设置异步信号监听，回收进程，防止僵尸进程出现
        \swoole_process::signal(SIGCHLD, [$this, 'listenEventWait']);
        $this->_lock = new \swoole_lock(SWOOLE_MUTEX);
    }

    /**
     * 开始
     */
    public function start()
    {
        $this->_lock->lock();
        try {
            $sizeWorker = $this->_max_worker_num - count($this->_workers);
            for ($n = 0; $n < $sizeWorker; $n++) {
                //统一子进程执行入口方法
                $process = new \swoole_process(['\\App\\worker\\CacheClearWorker', 'Start'], false, false);
                $chId = $process->start();
                $this->_workers[strval($chId)] = time();
            }
        } catch (\Exception $exception) {
            //todo 杀掉主进程??
        }
        $this->_lock->unlock();
    }

    /**
     * 子进程结束或者被kill,执行wait回收
     */
    public function listenEventWait()
    {
        while ($ret = \swoole_process::wait(false)) {//阻塞等待子进程退出，并回收子进程
            error_log(date('Y-m-d H:i:s') . "\tWorker Process {$ret['pid']} Quit!\n", 3, LOG_PATH . 'cache_log.txt');
            $this->_lock->lock();
            if (isset($this->_workers[strval($ret['pid'])])) {
                unset($this->_workers[strval($ret['pid'])]);
            }
            $this->_lock->unlock();
        }
        $this->start();
    }

    /**
     * 重启子进程
     */
    public function reboot()
    {
        $timeCurr = time();
        $pidWorkerItem = $this->_workers;
        error_log(date('Y-m-d H:i:s') . "\tReboot\n", 3, LOG_PATH . 'cache_log.txt');
        foreach ($pidWorkerItem as $pid => $timeStar) {
            //如果超过了5个小时，则杀掉子进程
            if ($timeCurr - $timeStar > (3600 * 5)) {
                \swoole_process::kill($pid);
                error_log(date('Y-m-d H:i:s') . "\tReboot $pid " . PHP_EOL, 3, LOG_PATH . 'cache_log.txt');
            }
        }
        $this->after();
    }

    /**
     * 启动1个小时之后重启
     */
    public function after()
    {
        $this->_after = swoole_timer_after(3600000, array($this, 'reboot'));
    }

    /**
     * 获取主进程名称
     * panda_process:log-kafka
     */
    public function getConsumerMp()
    {
        $prefix = $this->getMpNamePrefix().':%s';
        return sprintf($prefix,Config::getConfig()->master->clear_master_name);
    }


    /**
     * 获取主进程前缀名
     */
    public  function getMpNamePrefix()
    {
        return Config::getConfig()->panda_process;
    }

    /**
     * 检查获取主进程id
     */
    public static function getMpId()
    {
        return self::$_mpid;
    }
}

$mainProcess = new ClearCache();
//$mainProcess->start();
//$mainProcess->after();