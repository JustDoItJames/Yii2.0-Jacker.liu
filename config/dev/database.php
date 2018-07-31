<?php
return [
    'db' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=rds.jinfuzi.com;dbname=jfz_sns',
        'username' => 'jfzwww',
        'password' => 'gwkfznb',
        'charset' => 'utf8',
        'tablePrefix' => 'jfz_'
    ],
    'db_simu_master'=>[
        'class'=>'yii\db\Connection',
        'dsn' => 'mysql:host=rds.jinfuzi.com;dbname=jfz_simu4paipai',
        'username' => 'jfzwww',
        'password' => 'gwkfznb',
        'charset' => 'utf8',
        'tablePrefix' => 'jfz_',
    ],
    'predis' => [
        "class" => 'app\components\predis\RedisCluster',
        "nodes" => [
            'tcp://10.1.2.55:7000?alias=slave-01',
            'tcp://10.1.2.55:7001?alias=slave-01',
            'tcp://10.1.2.55:7002?alias=slave-01',
            'tcp://10.1.2.56:7000?alias=master',
            'tcp://10.1.2.56:7001?alias=master',
            'tcp://10.1.2.56:7002?alias=master'
        ],
        'options' => [
            'cluster' => 'redis',
            'prefix' => 'lwj.dev.'
        ]
    ],
    'redis' => [
        'class' => 'yii\redis\Connection',
        'hostname' => '10.1.2.53',
        'port' => 6379,
        "database" => 3,
        "connectionTimeout" => 5,
//        "prefix" => "jfz."
    ],
    'ocs' => [
        'class'=>'app\components\predis\RedisCache',
        'redis' => 'redis',
        'keyPrefix'=>'lwj.ocs.',
        'serializer'=>['\yii\helpers\Json::encode','\yii\helpers\Json::decode']
    ],
    'cache' => [//默认配置 文件缓存
        'class' => 'yii\caching\FileCache',
    ],
];