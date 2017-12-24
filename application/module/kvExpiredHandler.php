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
use App\lib\Config;
use App\PandaTaskServer;
use App\worker\job\ClearCache;

class kvExpiredHandler
{
    protected $_redis;
    protected $_arCache;
    protected $_worker;
    protected $_config;

    public function __construct()
    {
        $this->_redis = RedisCache::getSingleRedis(true, 'redis_list');
        $this->_arCache = RedisCache::getSingleRedis(false, 'redis_kv_expire');
        $this->_config = Config::getConfigArr('panda_server_section');
    }

    public function run(\swoole_process $worker)
    {

        $this->_worker = $worker;
        swoole_set_process_name($this->getProcessName());
        while (1) {
            $data = $this->_redis->rpopPon(RedisCacheClear::$_list_key_conf[ClearCache::KEY_EVENT_EXPIRED]);
            if (!(empty($data) || $data === false)) {
                echo "no empty!";
                $this->deleteExpireField($data);
            }
            //完成当前工作之后检查主进程是否还在
            $this->checkMainProcessIFexists();
        }
    }


    public function getProcessName()
    {
        return $this->getProcessPrefix().":".$this->_config['panda_process_child'];
    }

    public function getProcessPrefix()
    {
        return $this->_config['panda_process'];
    }

    public function deleteExpireField($data)
    {
        $list=explode(':',$data);
        $preList=array_reverse($list);
        list($expireKey) = $preList;
        list($uidAsField,$netbar_id) = explode('_',$expireKey);

        foreach (AppConf::EXPIRE_KEY as $hashKey) {
            $this->_arCache->hDel($hashKey,$uidAsField);
            error_log("KEY:[$hashKey] ,删除用户ID为：".$uidAsField."的用户!".PHP_EOL,3,'clear_record.log');
        }
    }

    /**
     * 检查主进程是否存在
     * @param $timerId
     * @param \swoole_process $worker
     */
    public function checkMainProcessIFexists()
    {
        $mpId = PandaTaskServer::getMpId();
        error_log('time:'.time().PHP_EOL,3,LOG_PATH.'PandaTaskServer.log');
        if(!\swoole_process::kill($mpId,0)){//父进程已经不存在,退出当前worker,回收进程资源
            error_log(date('Y-m-d H:i:s')."\t"."Message: PandaTaskServer Quit!",3,LOG_PATH.'PandaTaskServer.log');
            $this->_worker->exit();
        }
    }
}