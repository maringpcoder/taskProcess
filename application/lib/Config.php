<?php
/**
 * 获取环境配置/应用配置
 * Created by PhpStorm.
 * User: marin
 * Date: 2017/12/15
 * Time: 18:14
 */

namespace App\lib;


class Config
{

    public static function getConfig($iniFile=null)
    {
        $ini = $iniFile?$iniFile:ENV_INI_PATH.'env.ini';
        return self::parseIni($ini);
    }


    /**
     * 解析配置文件
     */
    protected static function parseIni($iniFile)
    {
        $iniContent = parse_ini_file($iniFile,true);
        return self::_arrayToObject($iniContent['env_section']);
    }

    private  static function _arrayToObject($configArr)
    {
        $object = new \stdClass();
        foreach ($configArr as $key=>$value){
            if(is_array($value)){
                $object->$key =  self::_arrayToObject($value);
            }else{
                $object ->$key = $value;

            }
        }
        return $object;
    }

}