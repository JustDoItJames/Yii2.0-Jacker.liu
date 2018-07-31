<?php
return [
    'class' => 'app\components\oss\OSS',
    'accessKeyId' => 'LTAIyHyBTyQZbGIK', // 阿里云OSS AccessKeyID
    'accessKeySecret' => 'zBbNTrU3rtLaGnn37FvGtVb6y1FS3d', // 阿里云OSS AccessKeySecret
    'bucket' => 'jfz-static2', // 阿里云的bucket空间
    'lanDomain' => 'oss-cn-hangzhou-internal.aliyuncs.com', // OSS内网地址
    'wanDomain' => 'oss-cn-hangzhou.aliyuncs.com', //OSS外网地址
    //'isInternal' => true // 上传文件是否使用内网，免流量费（选填，默认 false 是外网）
];