<?php

//$params = require __DIR__ . '/params.php';
//$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\commands',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        //rabbitMq组件
        'rabbitMq' => [
            'class' => 'app\components\amqp\AmqpWrapper',
            'host' => '10.1.2.101',
            'port' => 5672,
            'user' => 'jfzwww',
            'password' => 'jfzwww2018',
            'vhost' => 'jfzwww',
            //下面注销的代码，针对消费者的时候开启，针对生产者的时候关闭。
            //DefaultController::producter方法，作为生产者。
            //这里开启，是因为控制台的HelloController::consumer方法，作为消费者
            'read_write_timeout' => 60,
            'keepalive' => true,
            'heartbeat' => 30,
        ],
//        'db' => $db,
    ],
//    'params' => $params,
    /*
    'controllerMap' => [
        'fixture' => [ // Fixture generation command line.
            'class' => 'yii\faker\FixtureController',
        ],
    ],
    */
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
