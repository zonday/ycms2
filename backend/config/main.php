<?php
Yii::setPathOfAlias('root', __DIR__ . '/../..');
Yii::setPathOfAlias('common', __DIR__ . '/../../common');
Yii::setPathOfAlias('backend', __DIR__ . '/..');

$commonConfig = require(__DIR__ . '/../../common/config/main.php');

$default = array(
	'name' => 'YCMS Backend',
	'basePath' => 'backend',
	'preload' => array('bootstrap', 'log'),

	'import' => array(
		'application.components.*',
		'application.controllers.*',
		'application.models.*',
	),
	'components' => array(
		'bootstrap' => array(
			'class' => 'common.extensions.bootstrap.components.Bootstrap',
			'responsiveCss' => true,
		),
	),
);

return CMap::mergeArray(
	$commonConfig,
	$default,
	file_exists(__DIR__ . '/main-env.php') ? require(__DIR__ . '/main-env.php') : array(),
	file_exists(__DIR__ . '/main-local.php') ? require(__DIR__ . '/main-local.php') : array()
);