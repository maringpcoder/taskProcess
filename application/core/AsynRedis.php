<?php
/**
 * 异步redis客户端
 * Created by PhpStorm.
 * User: marin
 * Date: 2017/12/19
 * Time: 18:00
 */

namespace App\core;
use App\lib\Config;
class AsynRedis
{
    protected $_redis_async ;
    protected $_config=[];
    protected $_configObject=null;
    protected static $_AsynRedis = null;

    public function __construct(string $type='redis_list')
    {
        $this->_configObject = Config::getConfig('redis_env_section');
        $config = Config::getConfigArr('redis_env_section');
        if(isset($config[$type])){
            $this->_config = $config[$type];
        }
        if(empty($this->_config)){
            throw new \Exception('配置中缺少 ['.$type.']的配置');
        }
        $this->_redis = new \swoole_redis();
    }

    /**
     * @param $type ,redis队列
     * @param null $callback
     */
    public static function Single($type,$callback=null)
    {
        if(! (isset(self::$_AsynRedis[$type]) && (self::$_AsynRedis[$type] instanceof AsynRedis) ))
        {
            self::$_AsynRedis[$type] = new self($type);
        }
    }

    public function lpush($key,$value,$callback=null)
    {
        if(!$callback){
            $callback = function (\swoole_redis $redis_client,$result){
                if($result){
                    $redis_client->lpush();
                }
            };
        }
        $this->_redis ->connect($this->_config['host'],$this->_config['port'],$callback);
    }


    public function rpush()
    {}
}