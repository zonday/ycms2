<?php
Yii::setPathOfAlias('root', __DIR__ . '/../..');
Yii::setPathOfAlias('common', __DIR__ . '/../../common');

$default = array(
	'runtimePath' => __DIR__. '/../runtime',
	'language' => 'zh_cn',
	'timezone' => 'Asia/Shanghai',
	'import' => array(
		'common.components.*',
		'common.models.*',
		'common.extensions.yiidebugtb.*',
	),
	'components' => array(
		'db' => array(
			'schemaCachingDuration' => YII_DEBUG ? 0 : 86400000, // 1000 天
			'enableParamLogging' => YII_DEBUG,
			'charset' => 'utf8',
		),
		'urlManager' => array(
			'urlFormat' => 'path',
			'showScriptName' => false,
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
	)
);

return CMap::mergeArray(
	$default,
	file_exists(__DIR__ . '/main-env.php') ? require(__DIR__ . '/main-env.php') : array(),
	file_exists(__DIR__ . '/main-local.php') ? require(__DIR__ . '/main-local.php') : array()
);