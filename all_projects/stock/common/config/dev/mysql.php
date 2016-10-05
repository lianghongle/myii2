<?php
return [

	// default
	'db' => [
		'class' => 'yii\db\Connection',
		'dsn' => 'mysql:host=127.0.0.1;port=3306;dbname=stock',
		'username' => 'root',
		'password' => '123456',
		'tablePrefix' => '',
		'attributes' => [ ],
		'enableSchemaCache' => true,
		'schemaCacheDuration' => 3600,
		'schemaCacheExclude' => [ ],
		'schemaCache' => 'dbSchemaCache',
		'enableQueryCache' => false,
		'charset' => 'UTF8'
	],

];