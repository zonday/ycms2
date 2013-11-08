<?php
Yii::setPathOfAlias('root', __DIR__ . '/../..');
Yii::setPathOfAlias('common', __DIR__ . '/../../common');

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

$default = array(
	'runtimePath' => __DIR__. '/../runtime',
	'language' => 'zh_cn',
	'timezone' => 'Asia/Shanghai',
	'import' => array(
		'common.components.*',
		'common.components.behaviors.*',
		'common.components.widgets.*',
		'common.models.*',
		'common.models.core.*',
		'common.helpers.*',
		'common.extensions.yiidebugtb.*',
	),
	'components' => array(
		'format'=>array(
			'datetimeFormat'=>'Y-m-d h:i:s',
			'dateFormat'=>'Y-m-d',
			'booleanFormat'=>array('否','是'),
		),
		'db' => array(
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
		'settings' => CMap::mergeArray($defaultSettings, file_exists(__DIR__ . '/settings.php') ? require(__DIR__ . '/settings.php') : array())
	),
);

return CMap::mergeArray(
	$default,
	file_exists(__DIR__ . '/main-env.php') ? require(__DIR__ . '/main-env.php') : array(),
	file_exists(__DIR__ . '/main-local.php') ? require(__DIR__ . '/main-local.php') : array()
);