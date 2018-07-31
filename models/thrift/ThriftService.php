<?php

namespace app\models\thrift;


use app\components\utils\Code;
use app\components\utils\Tool;
use Thrift\Exception\TException;
use yii\base\Exception;

class ThriftService{
    public $service = '';
    protected $thrift = null;
    protected $errCode = null;
    protected $errMsg = '';
    protected $data = null;
    public function __construct($service=''){
        $service != '' && $this->service = $service;
        if($service == '' && $this->service == ''){
            throw new Exception('service is invalid', Code::CONF_MISS);
        }
        $ser = $this->service;
        // echo $ser;exit;
        try{
            $this->thrift = \Yii::$app->thrift->$ser;
        }catch(Exception $e){
            $this->errCode = $e->getCode();
            $this->errMsg = $e->getMessage();
            $this->thrift = null;

            \Yii::getLogger()->log($this->service.'['.$this->errCode.':'.$this->errMsg.']','error','thrift.'.get_class($this));
        }
    }
    //获取所有数据
    public function data(){
        return $this->data;
    }
    public function hasError(){
        return is_null($this->errCode);
    }
    //获取错误码
    public function getErrCode(){
        return $this->errCode;
    }
    //获取错误信息
    public function getErrMsg(){
        return $this->errMsg;
    }
    //获取检查数据的form model
    public function getFormModel($sign=''){
        return null;
    }
    //充值errCode,errMsg,data
    protected function reset(){
        $this->errCode = null;
        $this->errMsg = '';
        $this->data = null;
    }
    //参数检查
    public function check($method,$data){
        //待检查参数
        $param = array();
        foreach($data as $key=>$value){
            if(is_object($value)){
                //参数是结构体，单独进行检查
                $classReflect = new \ReflectionClass($value);
                $name = $classReflect->getName();
                $m = $this->getFormModel($name);
                $odata = array();
                foreach($value as $k=>$v){
                    if(is_object($v)){
                        //进行递归检查自己的结构体
                        $errors = $this->check($method,array($k=>$v));
                        if(!empty($errors)){
                            return $errors;
                        }
                        continue;
                    }
                    $odata[$k] = $v;
                }
                if($m != null && ($m->attributes = $odata) && !$m->validate()){
                    return $m->getErrors();
                }
                continue;
            }
            $param[$key] = $value;
        }
        if(empty($param)){
            return array();
        }
        $mmethod = $this->getFormModel($method);
        if($mmethod != null && ($mmethod->attributes = $param) && !$mmethod->validate()){
            return $mmethod->getErrors();
        }
        return array();
    }
    //调用接口
    public function invoke(){
        if($this->thrift == NULL){
            return False;
        }
        $this->reset();
        $args = func_get_args();
        $method = array_shift($args);
        //取得调用的方法信息
        $methodInfo = new \ReflectionMethod($this->thrift, $method);
        $data = array();
        foreach($methodInfo->getParameters() as $object){
            $data[] = $object->getName();
        }
        try{
            $error = call_user_func_array(array($this,'check'), array($method,array_combine($data, $args)));

        }catch(Exception $e){
            $this->errCode = Code::PARAM_ERR;
            $this->errMsg = $e->getMessage();
            \Yii::getLogger()->log($this->service.'/'.$method.':'.'['.json_encode($args).']['.$this->errCode.':'.$this->errMsg.']','error','thrift.'.get_class($this));
            return false;
        }
        if(!empty($error)){
            $this->errCode = Code::PARAM_ERR;
            foreach($error as $key=>$value){
                $this->errMsg != '' && $this->errMsg .= ';';
                $this->errMsg .= $key.':'.implode(',', $value);
            }
            return false;
        }
        try{
            $data = call_user_func_array(array($this->thrift,$method),$args);

        }catch(TException $e){
            $eJson = json_encode($e);
            isset($e->errCode) && $this->errCode = $e->errCode;
            isset($e->errMsg) && $this->errMsg = $e->errMsg;
            \Yii::getLogger()->log($this->service.'/'.$method.':'.'['.json_encode($args).']['.$eJson.']['.$this->errCode.':'.$this->errMsg.']','error','thrift.'.get_class($this));
            return false;
        }catch(\Exception $e){
            $eJson = json_encode($e);
            $this->errCode = $e->getCode();
            $this->errMsg = $e->getMessage();
            \Yii::getLogger()->log($this->service.'/'.$method.':'.'['.json_encode($args).']['.$eJson.']['.$this->errCode.':'.$this->errMsg.']','error','thrift.'.get_class($this));
            return false;
        }
        $this->data = Tool::objectToArray($data);
        //\Yii::getLogger()->log($this->service.'/'.$method.':'.'['.json_encode($args).']['.json_encode($this->data).']','error','thrift.'.get_class($this));
        return true;
    }
}
?>