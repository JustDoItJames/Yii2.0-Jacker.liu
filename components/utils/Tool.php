<?php
/**
 * 各种通用方法
 */

namespace app\components\utils;

use Yii;

class Tool
{
    /**
     * 对象转数组
     */
    public static function objectToArray($object)
    {
        if (!is_object($object) && !is_array($object)) {
            return $object;
        }
        $data = array();
        foreach ($object as $key => $value) {
            $data[$key] = self::objectToArray($value);
        }
        return $data;
    }
}

?>