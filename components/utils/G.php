<?php
namespace app\components\utils;

use app\components\utils\Uploadernew;

class G{

    /**
     * 对象转数组
     */
    public static function objectToArray($object){
        if(!is_object($object) && !is_array($object)){
            return $object;
        }
        $data = array();
        foreach($object as $key=>$value){
            $data[$key] = self::objectToArray($value);
        }
        return $data;
    }

    /**
     * 上传图片到ocs
     * @param $fieldName
     * @param $tmp_file @path="main/img"
     * @return null|string
     */
    public static function uploadImage($file,$path="main/img/",$allowFiles=array(".png", ".jpg", ".jpeg", ".gif", ".bmp"),$maxSize="2048000"){
        $href = "https://-static2.oss-cn-hangzhou.aliyuncs.com/";
        //$path = "/main/img/";
        $file = G::objectToArray($file);

        if (($filename = md5_file($file['tempName'])) === false) {
            return null;
        }
        if(@get_headers($href.$path.$filename)[0] != 'HTTP/1.1 404 Not Found'){
            return $href.$path.$filename;
        }

        $config = array(
            "pathFormat" => $path.$filename,
            "maxSize" => $maxSize,
            "allowFiles" => $allowFiles,
            'imageUrlPrefix' => $href
        );

        if(isset($file)&&$file['name']!=""){
            $up = new Uploadernew($file, $config, 'upload');
            return self::convertHttps($up->getStateInfo2());
        }
        return null;
    }

    /**
     * 将http的链接替换成https
     * @return 转换后的链接
     */
    public static function convertHttps($link)
    {
        if (empty($link)) {
            return null;
        }
        if (false !== strpos($link, 'http://')) {
            return str_replace('http://', 'https://', $link);
        }
        return $link;
    }
}
