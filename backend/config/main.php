<?php
return array(
	'basePath' => dirname(__FILE__) . '/..',
	'preload' => array('bootstrap','backend','log'),

	'import' => array(

	),

	'modules' => array(
		'gii' => array(
			'class' => 'system.gii.GiiModule',
			'password' => '123456',
		)
	),

	'components' => array(
		'backend' => array(
			'class' => 'YBackendComponent',
		),
		'crystal' => array(
			'class' => 'YCrystalComponent',
		),
		'image' => array(
			'class' => 'application.extensions.image.CImageComponent',
			'driver' => 'GD',
		),
		'bootstrap' => array(
			'class' => 'common.extensions.bootstrap.components.Bootstrap',
			'responsiveCss' => false,
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