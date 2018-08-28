<?php
return [
    'class'=>'app\components\utils\Restrict',
    'cacheID'=>'predis',
    'restricts'=>array(
        // 防刷接口： 10秒内访问接口100次，认定为刷接口
        'interfaceProtect'=>array(
            'ip'=> 100,
            'refer'=>'',
            'custom'=>100,
            'howlong'=>10
        ),
        // 社区上传图片防刷接口： 同一个ip限定200次，同一个uid访问接口限制请求100次
        'uploadProtect'=>array(
            'ip'=> 200,
            'refer' => '',
            'custom' => 100,
            'howlong' => 24*60*60
        ),
        //社区发帖条数：在一天内同一个ip只能发帖50次，同一个账号只能发帖50次
        'communityPost'=>array(
            'ip'=> 50,                                    //针对同一个ip地址限制次数
            'custom' => 50,                               //针对同一个账号限制次数
            'howlong' => 24*60*60,                        //过期时间
            'ipWhiteList' => [''],               //白名单
//            'ipBlackList' => ['127.0.0.1'],             //黑名单
            'refer' => '',
//            'refer' => '/.*\.lwj.*/',                   //refer的正则模式
        ),
    )
];
