<?php
Yii::setPathOfAlias('root', dirname(__FILE__) . '/../..');
Yii::setPathOfAlias('common', dirname(__FILE__) . '/../../common');

require dirname(__FILE__) . '/db.php';

$defaultSettings = array(
	'general' => array(
		'site_name' => array(
			'label' => '站点名称',
			'required' => true,
		),
		'site_keywords' => array(
			'label' => '站点关键字',
		),
		'site_description' => array(
			'label' => '站点描述',
			'type' => 'textarea',
		)
	),
	'system' => array(
		'admin_email' => array(
			'label' => '管理员Email',
			'type' => 'email',
			'required' => true,
		),
	)
);

return array(
	'name' => 'YCMS',
	'runtimePath' => dirname(__FILE__). '/../runtime',
	'language' => 'zh_cn',
	'timezone' => 'Asia/Shanghai',
	'import' => array(
		'common.components.*',
		'common.components.behaviors.*',
		'common.components.validators.*',
		'common.components.widgets.*',
		'common.models.*',
		'common.models.core.*',
		'common.models.content.*',
		'common.models.other.*',
		'common.helpers.*',
		'common.extensions.yiidebugtb.*',
		'application.components.*',
		'application.controllers.*',
		'application.models.*',
		'application.widgets.*',
	),
	'components' => array(
		'mailer'=>array(
			'class'=>'common.extensions.mailer.Mailer',
			'host'=>'smtp.qq.com',
			'username'=>'zhuanjiao.test@foxmail.com',
			'password'=>'zhuanjiao.test',
			'from'=>'zhuanjiao.test@foxmail.com',
		),
		'format'=>array(
			'datetimeFormat'=>'Y-m-d H:i:s',
			'dateFormat'=>'Y-m-d',
			'booleanFormat'=>array('否','是'),
		),
		'db' => array(
			'connectionString' => 'mysql:host='. DB_HOST .';dbname=' . DB_NAME,
			'username' => DB_USER,
			'password' => DB_PASSWORD,
			'tablePrefix' => DB_TABLE_PREFIX,
			'schemaCachingDuration' => YII_DEBUG ? 0 : 86400000, // 1000 天
			'enableParamLogging' => YII_DEBUG,
			'emulatePrepare' => true,
			'charset' => 'utf8',
		),
		'urlManager' => array(
			'urlFormat' => 'path',
			//'showScriptName' => false,
			'urlSuffix' => '/',
		),
		'cache' => extension_loaded('apc') ?
			array(
				'keyPrefix' => 'ycms',
				'class' => 'CApcCache',
			)
			:
			array(
				'keyPrefix' => 'ycms',
				'class' => 'system.caching.CFileCache',
			),
		'user'=>array(
			'allowAutoLogin'=>true,
		),
		'errorHandler'=>array(
			'errorAction' => YII_DEBUG ? null : 'site/error',
		),
		'log' => array(
			'class' => 'CLogRouter',
			'routes' => array(
				array(
					'class' => 'CFileLogRoute',
					'levels' => 'error, warning',
				),
				array(
					'class' => 'XWebDebugRouter',
					'config' => 'alignLeft, opague, runInDebug, fixedPos, collapsed, yamlStyle',
					'levels' => 'error, warning, trace, profile, info',
					'allowedIPs' => array('127.0.0.1', '::1'),
				)
			),
		),
	),

	'params'=>array(
		'settings' => CMap::mergeArray($defaultSettings, file_exists(dirname(__FILE__) . '/settings.php') ? require(dirname(__FILE__) . '/settings.php') : array())
	),
);