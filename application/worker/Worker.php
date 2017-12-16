<?php
namespace App\worker;
use App\worker\job\consumer;
/**
 * Created by PhpStorm.
 * User: marin
 * Date: 2017/12/16
 * Time: 14:02
 */
class Worker
{
    protected $_worker = null;
    protected $_workerItems = [];

    public function __construct(\swoole_process $worker)
    {
        $this->_worker = $worker;
        swoole_set_process_name(sprintf('panda_clear_cache:%s','worker'));
        //启用worker进程
        $this->loopWorker();
    }

    /**
     * 工作进程开始
     */
    public function loopWorker()
    {
        consumer::run();
    }


    public function Start(\swoole_process $worker)
    {
       new self($worker);
    }


}