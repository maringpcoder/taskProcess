<?php
/**
 * Created by PhpStorm.
 * User: marin
 * Date: 2017/12/15
 * Time: 17:08
 */
date_default_timezone_set('Asia/Shanghai');
defined('DS') || define('DS',DIRECTORY_SEPARATOR);
defined('ROOT_PATH') || define('ROOT_PATH',__DIR__.DS);
defined('SYSTEM_PATH') || define('SYSTEM_PATH',dirname(ROOT_PATH));
defined('ENV_INI_PATH') || define('ENV_INI_PATH',SYSTEM_PATH.DS);
defined('LOG_PATH') || define('LOG_PATH',ROOT_PATH.'log'.DS);
require_once(ROOT_PATH.DS.'..'.DS.'vendor/autoload.php');