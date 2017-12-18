<?php
/**
 * 获取环境配置/应用配置
 * 目前不支持读取二级配置,后期如有需要进行改造
 * Created by PhpStorm.
 * User: marin
 * Date: 2017/12/15
 * Time: 18:14
 */

namespace App\lib;


class Config
{

    /**
     * 根据配置文件获取节点中的配置项
     * @param null $section
     * @param null $iniFile
     * @return mixed
     */
    public static function getConfig($section=null,$iniFile=null)
    {
        $ini = self::getIniFile($iniFile);
        $section = $section??'log_section';
        return self::parseIni($ini,$section);
    }

    /**根据配置文件获取节点配置
     * @param null $section
     * @param null $iniFile
     * @return mixed
     */
    public static function getConfigArr($section=null,$iniFile=null)
    {
        $ini = self::getIniFile($iniFile);
        $section = $section??'log_section';
        return self::parseIni($ini,$section,false);
    }

    public static function getIniFile($iniFile)
    {
        return $ini = $iniFile?$iniFile:ENV_INI_PATH.'env.ini';
    }

    /**
     * @param $iniFile ,配置文件绝对路径
     * @param $section,'配置节点名'
     * @param $returnObj
     * @return mixed
     * 解析配置文件
     */
    protected static function parseIni($iniFile,$section='log_section',$returnObj=true)
    {
        $iniContent = parse_ini_file($iniFile,true);
        if($returnObj){
            return self::_arrayToObject($iniContent[$section]);
        }
        return $iniContent[$section];
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