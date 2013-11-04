<?php
return array(
	'components' => array(
		'db' => array(
			'connectionString' => 'mysql:host=127.0.0.1;dbname=ycms2',
			'username' => 'root',
			'password' => '',
			'tablePrefix' => 'y_',
		)
	),
	'modules' => array(
		'gii' => array(
			'class' => 'system.gii.GiiModule',
			'password' => '123456',
		)
	),
);