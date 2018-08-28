<?php
return [
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
