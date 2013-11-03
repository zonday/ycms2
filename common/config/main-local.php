<?php
return array(
	'components' => array(
		'db' => array(
			'connectionString' => 'mysql:host=127.0.0.1;dbname=ycms2',
			'username' => 'root',
			'password' => '123456',
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