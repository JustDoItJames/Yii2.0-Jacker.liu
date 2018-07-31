<?php
/**
 * Created by PhpStorm.
 * User: liuwenjie
 * Date: 2018/7/7
 * Time: 10:52
 */

namespace app\components\amqp;

use yii\base\Component;
use Yii;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use yii\base\Exception;

class AmqpWrapper extends Component
{
    //以下这些是AMQPStreamConnection的构造函数参数
    public $host = 'localhost';
    public $port = '5672';
    public $vhost = '/';
    public $user = 'guest';
    public $password = 'guest';
    public $insist = false;
    public $login_method = 'AMQPLAIN';
    public $login_response = null;
    public $locale = 'en_US';
    public $connection_timeout = 3.0;
    public $read_write_timeout = 3.0;
    public $context = null;
    public $keepalive = false;
    public $heartbeat = 0;

    public $channelId = null;

    /** @var AMQPStreamConnection */
    protected $_connection = false;

//    /** @var string */
//    protected $_exchangeName = '';
//
//    /** @var string */
//    protected $_queueName = '';
//
//    /** @var string */
//    protected $_routingKey = '';

    /** @var AMQPChannel */
    protected $_channel = false;

    /**
     * 初始化connection和channel
     */
    public function init()
    {
        $this->_connection = $this->getConnection();
        $this->_channel = $this->_connection->channel($this->channelId);
        parent::init();
    }

    /**
     * 获取连接
     * @author liuwenjie
     * @return AMQPStreamConnection
     * @throws Exception
     */
    public function getConnection() {
        if ($this->_connection === false) {
            $this->_connection = new AMQPStreamConnection(
                $this->host, $this->port, $this->user, $this->password, $this->vhost, $this->insist, $this->login_method, $this->login_response,
                $this->locale, $this->connection_timeout, $this->read_write_timeout, $this->context, $this->keepalive, $this->heartbeat
            );
        }

        if($this->_connection instanceof AMQPStreamConnection){
            return $this->_connection;
        }else{
            throw new Exception('can not get active AMQP connection!');
        }
    }

    /**
     * 获取channel
     * @author liuwenjie
     * @param int $channelId
     * @return AMQPChannel
     * @throws Exception
     */
    public function getChannel($channelId = null) {
        if ($this->_channel === false) {
            $this->_channel = $this->getConnection()->channel($channelId);
        }

        if($this->_channel instanceof AMQPChannel){
            return $this->_channel;
        }else{
            throw new Exception('can not get active AMQP channel!');
        }
    }

    /**
     * 生成消息
     * @param string $data
     * @param array $properties
     * @return AMQPMessage
     */
    public function createMessage($data , $properties = []) {
        $defaultProperties = [
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
        ];
        $properties = array_merge($defaultProperties, $properties);
        return new AMQPMessage($data, $properties);
    }

    /**
     * Publishes a message
     *
     * @param AMQPMessage $msg
     * @param string $exchange
     * @param string $routingKey
     * @param bool $mandatory
     * @param bool $immediate
     * @param int $ticket
     */
    public function publish($msg, $exchange = '', $routingKey = '', $mandatory = false, $immediate = false, $ticket = null){
        $this->getChannel()->basic_publish($msg, $exchange, $routingKey, $mandatory, $immediate, $ticket);
    }

    /**
     * 启动消费者，循环消费消息
     *
     * @param string $queue
     * @param string $consumerTag
     * @param bool $noLocal
     * @param bool $noAck
     * @param bool $exclusive
     * @param bool $nowait
     * @param callable|null $callback
     * @param int|null $ticket
     * @param array $arguments
     */
    public function consumeLoop(
        $queue = '', $consumerTag = '', $noLocal = false, $noAck = false, $exclusive = false,
        $nowait = false, $callback = null, $ticket = null, $arguments = array()
    ){
        $this->getChannel()
            ->basic_consume($queue, $consumerTag, $noLocal, $noAck, $exclusive, $nowait, $callback, $ticket, $arguments);

        while (count($this->getChannel()->callbacks)) {
            $this->getChannel()->wait();
        }
    }

    /**
     * 定义exchange
     *
     * @param string $exchange
     * @param string $type
     * @param bool $passive
     * @param bool $durable
     * @param bool $autoDelete
     * @param bool $internal
     * @param bool $nowait
     * @param array $arguments
     * @param int $ticket
     * @return self
     */
    public function exchangeDeclare(
        $exchange, $type, $passive = false, $durable = false, $autoDelete = true,
        $internal = false, $nowait = false, $arguments = array(), $ticket = null
    ){
        $this->getChannel()->exchange_declare($exchange, $type, $passive, $durable, $autoDelete, $internal, $nowait, $arguments, $ticket);

        return $this;
    }

    /**
     * 将 queue 绑定到 exchange
     *
     * @param string $queue
     * @param string $exchange
     * @param string $routingKey
     * @param bool $nowait
     * @param array $arguments
     * @param int $ticket
     * @return self
     */
    public function queueBind($queue, $exchange, $routingKey = '', $nowait = false, $arguments = array(), $ticket = null){
        $this->getChannel()->queue_bind($queue, $exchange, $routingKey, $nowait, $arguments, $ticket);
        return $this;
    }

    /**
     * 定义队列，如果需要的话会创建该队列
     *
     * @param string $queue
     * @param bool $passive
     * @param bool $durable
     * @param bool $exclusive
     * @param bool $autoDelete
     * @param bool $nowait
     * @param array $arguments
     * @param int $ticket
     * @return self
     */
    public function queueDeclare(
        $queue = '', $passive = false, $durable = false, $exclusive = false,
        $autoDelete = true, $nowait = false, $arguments = array(), $ticket = null
    ){
        $this->getChannel()->queue_declare($queue, $passive, $durable, $exclusive, $autoDelete, $nowait, $arguments, $ticket);

        return $this;
    }

    /**
     * 通过发送一个NACK给消息服务器来拒绝该条消息（注意，消息不会重新回到队列）
     * Rejects a message sending a NACK to the message broker.
     * @param AMQPMessage $message
     */
    public function nack($message)
    {
        /** @var AMQPChannel $channel */
        $channel = $message->delivery_info['channel'];
        return $channel->basic_nack($message->delivery_info['delivery_tag']);
    }

    /**
     * 消息重新排队
     * 通过发送一个NACK给消息服务器来拒绝该条消息（注意，消息会重新回到队列，重新排队）
     * Rejects a message sending a NACK to the message broker.
     * @param AMQPMessage $message
     */
    public function requeue($message)
    {
        /** @var AMQPChannel $channel */
        $channel = $message->delivery_info['channel'];
        return $channel->basic_nack($message->delivery_info['delivery_tag'], false, true);
    }

    /**
     * 通过发送一个ACK给消息服务器来确认完成该条消息
     * @param AMQPMessage $message
     */
    public function ack($message)
    {
        /** @var AMQPChannel $channel */
        $channel = $message->delivery_info['channel'];
        $channel->basic_ack($message->delivery_info['delivery_tag']);
    }

    /**
     * 关闭连接
     */
    public function close() {
        if($this->_channel){
            $this->_channel->close();
        }

        if ($this->_connection) {
            $this->_connection->close();
        }
    }

    public function __sleep() {
        $this->close();
        return array_keys(get_object_vars($this));
    }

    public function __destruct() {
        $this->close();
    }
}