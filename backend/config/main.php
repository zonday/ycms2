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
		'application.widgets.*',
	),

	'modules' => array(
		'auth' => array(
			'class' => 'application.modules.auth.YAuthModule'
		),
	),

	'components' => array(
		'crystal' => array(
			'class' => 'YCrystalComponent',
		),
		'image' => array(
			'class' => 'application.extensions.image.CImageComponent',
			'driver' => 'GD',
		),
		'bootstrap' => array(
			'class' => 'common.extensions.bootstrap.components.Bootstrap',
			'responsiveCss' => true,
			'fontAwesomeCss' => true,
		),
		'authManager' => array(
			'class'=>'CDbAuthManager',
			'itemTable' => '{{authitem}}',
			'assignmentTable' => '{{authassignment}}',
			'itemChildTable' => '{{authitemchild}}',
		),
	),
);

return CMap::mergeArray(
	$commonConfig,
	$default,
	file_exists(__DIR__ . '/main-env.php') ? require(__DIR__ . '/main-env.php') : array(),
	file_exists(__DIR__ . '/main-local.php') ? require(__DIR__ . '/main-local.php') : array()
);