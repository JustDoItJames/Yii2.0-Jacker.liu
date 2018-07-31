<?php
return [
    'traceLevel' => YII_DEBUG ? 3 : 0,//消息跟踪
    'targets' => [
        [
            //'class' => 'yii\log\FileTarget',//文件存储
            'class' => 'app\components\logs\FileTarget',
            'levels' => ['error'],//错误类型
            'logFile' => '@app/runtime/logs/error/app.log',//存储位置
            //'enableDatePrefix' => true,
            'logVars' => [],//上下文信息
            'maxFileSize' => 5120, //文件大小5M
            'maxLogFiles' => 50 //文件上限
            //'categories' => ['yii\db\*','app\controllers\*'], 过滤
            //'except' => ['app\controllers\SiteController:*'] 过滤
        ],
        [
            //'class' => 'yii\log\FileTarget',//文件存储
            'class' => 'app\components\logs\FileTarget',
            'levels' => ['profile'],//性能分析类型
            'logFile' => '@app/runtime/logs/profile/app.log',//存储位置
            //'enableDatePrefix' => true,
            'logVars' => [],//上下文信息
            'maxFileSize' => 5120, //文件大小5M
            'maxLogFiles' => 50 //文件上限
            //'categories' => ['yii\db\*','app\controllers\*'], 过滤
            //'except' => ['app\controllers\SiteController:*'] 过滤
        ],
        [
            'class' => 'app\components\logs\FileTarget',
            'levels' => ['warning'],
            'logFile' => '@app/runtime/logs/warning/app.log',
            //'enableDatePrefix' => true,
            'logVars' => [],
            'maxFileSize' => 5120,
            'maxLogFiles' => 50
        ],
        [
            'class' => 'app\components\logs\FileTarget',
            'levels' => ['info'],
            'logFile' => '@app/runtime/logs/info/app.log',
            //'enableDatePrefix' => true,
            'logVars' => [],
            'maxFileSize' => 5120,
            'maxLogFiles' => 50
        ],
        [
            'class' => 'app\components\logs\FileTarget',
            'levels' => ['trace'],
            'logFile' => '@app/runtime/logs/trace/app.log',
            //'enableDatePrefix' => true,
            'logVars' => [],
            'maxFileSize' => 5120,
            'maxLogFiles' => 50
        ],
        /*[
            'class' => 'yii\log\DbTarget',//数据库存储日志
            'db' => 'db',
            'logTable' => 'jfz_log',
            'logVars' => [],
            'categories' => [//过滤
                'yii\db\*',
            ],
        ],
        [
            'class' => 'yii\log\EmailTarget',//邮件发送日志
            'levels' => ['error'],
            'categories' => ['yii\db\*'],
            'message' => [
                'from' => ['log@example.com'],
                'to' => ['admin@example.com', 'developer@example.com'],
                'subject' => 'Database errors at example.com',
            ],
        ],*/
    ]
];