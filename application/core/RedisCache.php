<?php

/**
 * redis缓存驱动
 * Created by PhpStorm.
 * User: marin
 * Date: 2017/12/18
 * Time: 17:17
 */

namespace App\core;

use App\lib\Config;

class RedisCache
{
    protected $_config = [];
    protected static $_instance = [];
    /** @var $_redis \Redis  */
    protected $_redis = null;
    /** @var $_predis \Redis */

    protected $_predis= null;
    protected $_conn = false;

    public function __construct($config,$pconnect=false)
    {
        $this->_config = $config;
        if (!$pconnect){
            $this->connect();
        }
        $this ->pconnect();

    }

    public static function getSingleRedis($pconnect=false,$type = 'redis_kv_expire')
    {
        if (!isset(self::$_instance[$type])) {
            $config = Config::getConfigArr('redis_env_section');
            if ($config) {
                isset($config[$type]) ? $config[$type] : [];
            }
            if (empty($config)) {
                throw new \Exception('redis instance type=> ' . $type . ':配置不存在!');
            }
            self::$_instance[$type] = new self($config,$pconnect);
        }
        return self::$_instance[$type];
    }


    public function connect()
    {

        try {
            if($this->_redis){
                @$this->_redis->close();
            }

            $this->_redis = new \Redis();
            if ($this->_config['socket_type'] === 'unix') {
                $success = $this->_redis->connect($this->_config['socket']);
            } else {
                $success = $this->_redis->connect($this->_config['host'], $this->_config['port'], $this->_config['timeout']);
            }

            if (!$success) {
                $this->conn = false;
            } elseif (isset($this->_config['password']) && $this->_config['password'] && !$this->_redis->auth($this->_config['password'])) {
                $this->conn = false;
            } else {
                $this->conn = true;
            }
        } catch (\RedisException $e) {
            $this->conn = false;
        }
    }



    public function pconnect()
    {
        try {
            if($this->_redis){
                @$this->_redis->close();
            }

            $this->_predis = new \Redis();
            $this->_predis->pconnect($this->_config['host'],$this->_config['password'],$this->_config['timeout']);
            $this->conn = true;
        } catch (\RedisException $e) {
            $this->conn = false;
        }
    }

    /**
     * @param $channelName ,订阅的频道名称
     * @param $callbackArr ,回调处理
     * @throws \Exception
     *
     */
    public function subscribe($channelName,$callbackArr)
    {
        try {
            if ($this->_conn) {
                $this->_predis->subscribe([$channelName], $callbackArr);
            }
        }catch (\RedisException $exception){
            throw new \Exception($exception->getMessage(),$exception->getCode());
        }
    }

}