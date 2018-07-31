<?php
/**
 * predis组件自定义封装
 * User: liuwenjie
 * Date: 2016/1/5
 * Time: 16:04
 */

namespace app\components\predis;

use Predis\Client;
use yii\base\Component;

class RedisCluster extends Component{

    public $nodes = [];
    public $options = [];
    private static $_INSTANCE = null;

    public function init(){
        if(self::$_INSTANCE == null){
            self::$_INSTANCE = new Client($this->nodes,$this->options);
        }
        return self::$_INSTANCE;
    }

    /**
     * {@inheritdoc}
     */
    public function __call($commandID, $arguments)
    {
        return self::$_INSTANCE->executeCommand(
            self::$_INSTANCE->createCommand($commandID, $arguments)
        );
    }

}