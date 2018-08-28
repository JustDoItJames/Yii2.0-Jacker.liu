<?php
return [
    'thrift'=>array(
        'class'=>'app\components\thrift\ThriftClientOrigin',
        'serviceConfig'=>array(
            'ThriftUserInfoService'=>array(
                'dirName'           => 'userinfocenter111',
                'className'         => 'ThriftUserInfoService111',
                'serverHost'        => '',
                'serverPort'        => '',
                'sendTimeout'       => 15,
                'recvTimeout'       => 15,
                'maxConnectTimes'   => 2,
            ),
        )
    )
];
