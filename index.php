<?php
/**
 * 主进程,管理所有的子进程
 * Created by PhpStorm.
 * User: marin
 * Date: 2017/12/15
 * Time: 11:23
 */
namespace App;


use App\lib\Config;

include_once('./application/bootstrap.php');

class MasterProcess{
    protected $_worker_num = 0;//worker进程数
    protected $_lock=null;
    protected static $_mpid = 0;
    protected $_workers=[];//保存worker进程
    protected $_process_worker = [
        '\\App\\worker'
    ];
    protected $_after=null;

    public function __construct()
    {
        swoole_set_process_name('panda_process_manager');
        $this->_worker_num = Config::getConfig()->process->worker_num;
        self::$_mpid = posix_getpid();
        //设置异步信号监听，回收进程，防止僵尸进程出现
        \swoole_process::signal(SIGCHLD,[$this,'listenEventWait']);
        $this->_lock = new \swoole_lock(SWOOLE_MUTEX);
    }

    /**
     * 开始
     */
    public function start()
    {
        $this->_lock->lock();
        try{
            $sizeWorker = $this->_worker_num - count($this->_workers);
            //消费者进程
            for ($n=0 ; $n<$sizeWorker;$n++){
                for ($m=0;$m<count($this->_process_worker);$m++){
                    //统一子进程执行入口方法
                    $process = new \swoole_process([$this->_process_worker[$m].'\\Worker','Start'],false,false);
                    $chId = $process ->start();
                    $this->_workers[strval($chId)] = time();
                }
            }

        }catch (\Exception $exception){

        }
        $this->_lock->unlock();
    }

    /**
     * 子进程结束或者被kill,执行wait回收
     */
    public function listenEventWait()
    {
        while($ret=\swoole_process::wait(false)){//阻塞等待子进程退出，并回收
            error_log(date('Y-m-d H:i:s')."\tWorker Process {$ret['pid']} Quit!\n",3,LOG_PATH.'log.txt');
            $this->_lock->lock();
                if(isset($this->_workers[strval($ret['pid'])])){
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
        error_log(date('Y-m-d H:i:s')."\tReboot\n",3,LOG_PATH.'log.txt');
        foreach ($pidWorkerItem as $pid => $timeStar){
            //如果超过了5个小时，则杀掉子进程
            if($timeCurr - $timeStar > (3600*5))
            {
                \swoole_process::kill($pid);
                error_log(date('Y-m-d H:i:s')."\tReboot $pid ".PHP_EOL,3,LOG_PATH.'log.txt');
            }
        }
        $this ->after();
    }

    /**
     * 定时检查子进程，如果子进程超出了5个小时 则杀掉子进程
     */
    public function after()
    {
        $this->_after = swoole_timer_after(3600000,array($this,'reboot'));
    }
}

$mainProcess = new MasterProcess();
$mainProcess->start();