<?php

use Src\Container;
require_once 'vendor/autoload.php';

/**
 * Test Class
 */
class Test 
{
    /**
     *  hello function
     * @param  string $msg
     * @return string
     */
    public function hello(string $msg = null): string
    {
        $res = $msg ?? "Hello world";
        return $res;
    }   
};
/**
 * Logger Class
 */
class Logger
{
    public function __construct(
        public string $name = 'Logger',
    )
        {
    }
    public function log(string $msg = null)
    {
        return "$msg from $this->name" . PHP_EOL;
    }
};

/**
 * Database Class
 */
class Database
{
    public function __construct(
        private string $host, 
        private string $username, 
        private string $password, 
        private string $dbname)
    {
    }

    public function query(string $sql): array
    {
        $data = [
            'db' => [
                'host' => $this->host,
                'username' => $this->username,
                'password' => $this->password,
                'dbname' => $this->dbname,
            ],
            'sql' => $sql,
        ];
        return ['data' => $data];
    }
}

/**
 * UserController Class
 * Main Class we will use to test the container
 */
class UserController
{
    public function __construct(
        private Logger $logger, 
        private Test $test, 
        private Database $database)
    {
    }

    /**
     * test function
     * @return void
     */
    public function test(): void
    {
        print $this->test->hello('UserController Index' . PHP_EOL . PHP_EOL);

    }

    /**
     * database function
     * @return void
     */
    public function database(): void
    {
        print_r($this->database->query('SELECT * FROM users'));
    }

    /**
     * log function
     * @param  string $msg
     * @return void
     */
    public function log(string $msg = null): void
    {   
        $msg = $msg ?? "Hello world From Logger";
        print $this->logger->log($msg);
    }
}


$container = new Container();
$container->set('config', function () {
    return [
        'db' => [
            'driver' => 'pgsql',
            'host' => 'postgres',
            'port' => '5432',
            'database' => 'fastphp',
            'username' => 'zataca',
            'password' => 'zataca',
        ]
    ];
});

$container->set('test', fn()=> new Test ());
$container->set('app', fn () => 'deu');
$container->set('logger', fn () => new Logger());


$container->set(Database::class, function ($container) {
    $config = $container->get('config')['db'];
    return new Database(
        $config['driver'],
        $config['host'],
        $config['port'],
        $config['username'],
        $config['password'],
        $config['database']
    );
});

/**
 * @param Container $container
 * @return UserController
 * bind UserController to the container using the get method to resolve dependencies
 * 
 * 
 */
 $container->set('userController', function ($container) {
        $logger = $container->get('logger');
        $test = $container->get('test');
        $database = $container->get(Database::class);
        return new UserController($logger, $test, $database);
    });
    $user = $container->get('userController');
    $user->test();
    $user->database();
    $user->log("Fer");

    print_r("--------------------------------------" . PHP_EOL . PHP_EOL);



 /**
  * @param Container $container
    * @return UserController
    * bind UserController to the container using the build method to resolve dependencies
  */
$user = $container->build(UserController::class);
$user->test();
$user->database();
$user->log('Hello world from Logger throw UserController Class using build method to resolve dependencies');


die();














$container->set('test', function () {
        return "Hello";
    });

$container->set('app', fn() => 'deu');
$container->set('logger', fn() => new Logger());

$test = $container->get('test');
$app = $container->get('app');
$log = $container->get('logger');
$msg = $log->log('Hello world throw Logger');

$msg = $container->getAll();
var_dump($msg);die();
var_dump($test, $app, $log);