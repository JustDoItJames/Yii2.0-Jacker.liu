<?php
namespace app\components\predis;
use Yii;
use yii\helpers\StringHelper;

class RedisCache extends \yii\redis\Cache{
    public $hasKey = false;
    public function buildKey($key)
    {
        if (is_string($key)) {
            $key = (ctype_alnum($key) && StringHelper::byteLength($key) <= 32) || !$this->hasKey ? $key : md5($key);
        } else {
            $key = md5(json_encode($key));
        }

        return $this->keyPrefix . $key;
    }
}