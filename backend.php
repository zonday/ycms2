<?php
$yii=dirname(__FILE__).'/../yii1.1.4/framework/yii.php';

defined('YII_DEBUG') or define('YII_DEBUG',true);
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);

require_once($yii);

$commonConfig=require_once(dirname(__FILE__).'/common/config/main.php');
$backendConfig=require_once(dirname(__FILE__).'/backend/config/main.php');

Yii::createWebApplication(CMap::mergeArray($commonConfig, $backendConfig))->run();