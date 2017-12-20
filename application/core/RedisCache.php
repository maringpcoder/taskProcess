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
        }else{
            $this ->pconnect();
        }
    }

    /**
     * @param bool $pconnect
     * @param string $type
     * @return RedisCache|mixed
     * @return mixed
     *
     */
    public static function getSingleRedis($pconnect=false,$type = 'redis_kv_expire')
    {
        try {
            if (!isset(self::$_instance[$type]) || !(self::$_instance[$type] instanceof RedisCache)) {
                $config = Config::getConfigArr('redis_env_section');
                if ($config) {
                    isset($config[$type]) ? $config[$type] : [];
                }
                if (empty($config)) {
                    throw new \Exception('redis instance type=> ' . $type . ':配置不存在!');
                }
                return self::$_instance[$type] = new self($config[$type], $pconnect);
            }
            return self::$_instance[$type];
        }catch (\Exception $exception){
            throw new \Exception($exception->getMessage(),$exception->getCode());
        }
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
                $this->_conn = false;
            } elseif (isset($this->_config['password']) && $this->_config['password'] && !$this->_redis->auth($this->_config['password'])) {
                $this->_conn = false;
            } else {
                $this->_conn = true;
            }
        } catch (\RedisException $e) {
            $this->_conn = false;
        }
    }



    public function pconnect()
    {
        try {
            $this->_predis = new \Redis();
            $this->_predis->pconnect($this->_config['host'],intval($this->_config['password']));
            $this->_conn = true;
        } catch (\RedisException $e) {
            $this->conn = false;
            throw new \Exception($e->getMessage(),$e->getCode());
        }
    }

    /**
     * @param $channelName ,订阅的频道名称
     * @param $callbackArr ,回调处理
     * @throws \Exception
     *
     */
    public function subscribe(array $channelName,callable $callbackArr)
    {
        try {
            if ($this->_conn) {
                $this->_predis->subscribe($channelName, $callbackArr);
            }else{
                var_dump($this->_conn);
                echo "Connect fail \n";
            }
        }catch (\RedisException $exception){
            throw new \Exception($exception->getMessage(),$exception->getCode());
        }
    }

    public function unSubscribe()
    {
        $this->_predis->close();
    }

    /**
     * @param $key
     * @param int $value
     */
    public function lpush($key,$value=1)
    {
        $this->_redis->lPush($key,serialize($value));//serialize有性能开销,实际如队列需要改写一下这里的逻辑,处理一下value,建议先打包一下
    }

}