<?php
return [
    'thrift'=>array(
        'class'=>'app\components\thrift\ThriftClientOrigin',
        'serviceConfig'=>array(
            /* 用户中心相关服务 */
            'ThriftUserInfoService'=>array(
                'dirName'           => 'userinfocenter',
                'className'         => 'ThriftUserInfoService',
                'serverHost'        => '10.1.2.191',
                'serverPort'        => '50599',
                'sendTimeout'       => 15,
                'recvTimeout'       => 15,
                'maxConnectTimes'   => 2,
            ),
        )
    )
];