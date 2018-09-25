<?php 
    require '../vendor/autoload.php';
    use Illuminate\Database\Capsule\Manager as Capsule;
    use Yajra\Oci8\Connectors\OracleConnector;
    use Yajra\Oci8\Oci8Connection;

    $capsule = new Capsule;

    $manager = $capsule->getDatabaseManager();
    $manager->extend('oracle', function($config)
    {
        $connector = new OracleConnector();
        $connection = $connector->connect($config);
        $db = new Oci8Connection($connection, $config["database"], $config["prefix"]);
        // set oracle date format to match PHP's date
        $db->setDateFormat('dd/MM/yyyy HH24:MI:SS');
        return $db;
    });

	// db config to oracle connection
	$dbconfig =  [ 
        'driver'=>'oracle',
        'tns'           => env('DB_TNS', ''),
        'host'          => env('DB_HOST', ''),
        'port'          => env('DB_PORT', '1521'),
        'database'      => env('DB_DATABASE', ''),
        'username'      => env('DB_USERNAME', ''),
        'password'      => env('DB_PASSWORD', ''),
        'charset'       => env('DB_CHARSET', 'AL32UTF8'),
        'prefix'        => env('DB_PREFIX', ''),
        'prefix_schema' => env('DB_SCHEMA_PREFIX', ''),
    ];
    
    $capsule->addConnection($dbconfig);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();