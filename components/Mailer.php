<?php
namespace app\components;

use yii\base\component;
use app\components\events\MessageEvent;

class Mailer extends Component
{
    const EVENT_MESSAGE_SENT = 'messageSent';

    public function send($message){
        //发送message的逻辑
        $event = new MessageEvent();//一个事件类，继承Event类
        $event->message = $message;
        $this->trigger(self::EVENT_MESSAGE_SENT,$event);
    }



}