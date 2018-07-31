<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use yii\console\Controller;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HelloController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     */
    public function actionIndex($message = 'hello world')
    {
        echo $message . "\n";
    }

    //消费者：用于消费消息 ---rabbitMq
    public function actionConsumer(){
        /** @var \app\components\amqp\AmqpWrapper $rabbitMq*/
        $rabbitMq = \Yii::$app->rabbitMq;
        $rabbitMq->exchangeDeclare('test_exchange', 'direct', false, true, false)
            ->queueDeclare('test_queue', false, true, false, false)
            ->queueBind('test_queue', 'test_exchange', 'test_routing')
            ->consumeLoop('test_queue', '', false, false, false, false, [$this, 'processMessage']);
    }

    /**
     * 消费者回调函数
     * 处理消息
     * @param AMQPMessage $message
     */
    public function processMessage($message) {
        $msg = $message->body;
        $msg = json_decode($msg, true);
        var_dump($msg);
//        self::reportData(json_decode($msg, true));
        //self::reportDataTest(json_decode($msg, true));

        /** @var \app\components\amqp\AmqpWrapper $rabbitMq*/
        $rabbitMq = \Yii::$app->rabbitMq;
        $rabbitMq->ack($message); //手动发送ACK应答
    }
}
