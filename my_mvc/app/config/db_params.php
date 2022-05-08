<?php 

	return array(		

		"pdo" => [

			'my_server1_pdo' => [
				'host' => '127.0.0.1',
				'database' => 'test',
				'user' => 'user',
				'password' => '12345',
			],
			'my_pgsql_server_pdo' => [
				'host' => '127.0.0.1',
				'database' => 'test',
				'port' => '5432',
				'user' => 'postgres',
				'password' => '12345',
			],

		],

		"mysql" => [

		],

		"pgsql" => [
			
			'my_pgsql_server' => [
				'host' => '127.0.0.1',
				'database' => 'test',
				'port' => '5432',
				'user' => 'postgres',
				'password' => '12345',
			],

		],

		"oracle" => [

		],

		"mssql" => [

			'my_mssql_server_pdo' => [
				'host' => '127.0.0.1:49170',
			    'database' => 'test',
			    'user' => 'test',
			    'password' => '12345',
			],

		],

	);

 ?>