<?php
define("CONFIG_DIR", __DIR__ . DIRECTORY_SEPARATOR . YII_ENV);

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@foo'   => '/path/to/foo',//定义别名，调用：Yii::getAlias('@foo')
    ],
    'timeZone' => 'Asia/Shanghai',
//    'catchAll' => ['site/error'],
//    'defaultRoute' => 'common/default',
    'components' => array_merge(
        [
            'request' => [
                // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
                'cookieValidationKey' => 'hlu2HXIbR2ZNL8TjICjxhL0gwIcTnPq0',
            ],
//            'cache' => [
//                'class' => 'yii\caching\FileCache',
//            ],
            'user' => [
                'identityClass' => 'app\models\User',
                'enableAutoLogin' => true,
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
                //这里不开启，DefaultController::producter方法，作为生产者。
                //是因为控制台的HelloController::consumer方法，作为消费者
//                'read_write_timeout' => 60,
//                'keepalive' => true,
//                'heartbeat' => 30,
            ],
            'errorHandler' => [
                'errorAction' => 'site/error',
            ],
            'mailer' => [
                'class' => 'yii\swiftmailer\Mailer',
                // send all mails to a file by default. You have to set
                // 'useFileTransport' to false and configure a transport
                // for the mailer to send real emails.
                'useFileTransport' => true,
            ],
            'Curl' => [
                'class' => 'app\components\utils\Curl',
                'options' => array(
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_CONNECTTIMEOUT => 6,
                    CURLOPT_TIMEOUT => 6,
                ),
            ],
            'log' => require(dirname(__FILE__) . '/log.php'),
            'Restrict' => require(dirname(__FILE__) . '/restrictRules.php'),
            'urlManager' => [
                'enablePrettyUrl' => true,
                'showScriptName' => false,
                'rules' => [
                    '/' => 'common/default/index',
                ],
            ],
            'oss' => require (dirname(__FILE__) . '/alioss.php'),
        ],
        require(CONFIG_DIR . DIRECTORY_SEPARATOR . 'components.php')
    ),
//    'params' => array_merge(
//
//    ),

    //模块配置
    'modules' => [
        'common' => [
            'class' => 'app\modules\common\CommonModule'
        ],
    ]
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
