<?php
use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => MYSQL_HOST_MAIN,
    'database'  => MYSQL_MAIN_DATABASE,
    'username'  => MYSQL_MAIN_USER,
    'password'  => MYSQL_MAIN_PASSWD,
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

// Set the event dispatcher used by Eloquent models (optional)
//use Illuminate\Events\Dispatcher;
//use Illuminate\Container\Container;
//$capsule->setEventDispatcher(new Dispatcher(new Container));

// Make this Capsule instance available globally.
$capsule->setAsGlobal();
$capsule->bootEloquent(); 
