<?php
namespace App\core;
use App\lib\Config;
/**
 * 异步redis客户端
 * Created by PhpStorm.
 * User: marin
 * Date: 2017/12/19
 * Time: 18:00
 */


class AsynRedis
{
    protected $_redis_async_client ;
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
        $this->_redis_async_client = new \swoole_redis();
    }

    /**
     * 订阅消息端切勿使用该方法
     * @param $type ,redis队列
     * @param null ,$callback
     * @return self
     */
    public static function Single($type)
    {
        if(! (isset(self::$_AsynRedis[$type]) && (self::$_AsynRedis[$type] instanceof AsynRedis) ))
        {
            self::$_AsynRedis[$type] = new self($type);
        }
        return self::$_AsynRedis[$type];
    }

    public function lpush($key,$value,$callback=null)
    {
        if(!$callback){

            $callback = function (\swoole_redis $redis_client,$result)use($key,$value){

                if($result!==false){
                    return $redis_client->lpush($key,$value,function(\swoole_redis $client ,$res){

                        if($res){
                            $client->close();
                        }

                    });

                }else{//链接redis server 失败
                    var_dump($result);
                    return array('code'=>$redis_client->errCode,'msg'=>$redis_client->errMsg);
                }
            };
        }

        $this->_redis_async_client ->connect($this->_config['host'],intval($this->_config['port']),$callback);
    }


    public function rpush($key,$value,$callback=null)
    {
        if(!$callback){
            $callback = function (\swoole_redis $redis_client,$result)use($key,$value){
                if($result){
                    $redis_client->lpush($key,$value,function(\swoole_redis $client ,$res){
                        //todo 加入操作redis队列成功
                    });
                }
            };
        }
        $this->_redis_async_client ->connect($this->_config['host'],$this->_config['port'],$callback);
    }
}