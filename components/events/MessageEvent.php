<?php
namespace app\components\events;

use yii\base\Event;

class MessageEvent extends Event
{
    public $message;

    public static function handlers($event){
        var_dump($event);exit;
    }
}