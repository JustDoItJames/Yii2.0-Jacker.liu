CAMQP Extension
===============

    php-amqplib/php-amqplib 的相关文档 ：https://github.com/php-amqplib/php-amqplib

Requirements
------------

- php-amqplib/php-amqplib


Installation
------------

 - 配置成component
      'components' => array(
        
        ...
        
        'rabbitMq' => [
            'class' => 'app\components\amqp\AmqpWrapper',
            'host' => '10.1.2.101',
            'port' => 5672,
            'user' => 'jfzwww',
            'password' => 'jfzwww2018',
            'vhost' => 'jfzwww',
            'read_write_timeout' => 60,
            'keepalive' => true,
            'heartbeat' => 30,
        ],
        
        ...
        
      )
      
 - Enjoy!
 
 
Usage:
-------

 生产者:
    $msg = \Yii::$app->rabbitMq->createMessage(json_encode($data, JSON_UNESCAPED_UNICODE));

    \Yii::$app->rabbitMq
        ->exchangeDeclare('baidu_ocpc_exchange', 'direct', false, true, false)
        ->publish($msg, 'baidu_ocpc_exchange', 'baidu_ocpc_routing');

  消费者:
     \Yii::$app->rabbitMq->exchangeDeclare(self::EXCHANGE_NAME, 'direct', false, true, false)
         ->queueDeclare(self::QUEUE_NAME, false, true, false, false)
         ->queueBind(self::QUEUE_NAME, self::EXCHANGE_NAME, self::ROUTING_KEY)
         ->consumeLoop(self::QUEUE_NAME, '', false, false, false, false, [$this, 'processMessage']);

     确认消息：
        \Yii::$app->rabbitMq->ack($message);

  exchangeDeclare，queueDeclare，queueBind，consumeLoop的具体使用参考源码(AmqpWrapper.php)，以及php-amqplib/php-amqplib的文档


