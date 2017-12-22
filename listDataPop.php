<?php
/**
 * Created by PhpStorm.
 * User: marin
 * Date: 2017/12/21
 * Time: 18:36
 */
namespace App;
use App\server\PandaTaskServer;

include_once('./application/bootstrap.php');
$pTaskServer = new PandaTaskServer();
$pTaskServer ->Start();