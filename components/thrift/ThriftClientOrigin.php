<?php
namespace app\components\thrift;
use app\components\util\G;
use yii\base\Component;
use yii\base\Exception;

class ThriftClientOrigin extends Component
{
    public $serverHost = 'localhost';
    public $serverPort = '9090';
    public $sendTimeout = 5;
    public $recvTimeout = 5;
    public $serviceConfig = [];
    
    private $services = array();
    private $loader;
    
    public function init()
    {
        $genDir = __DIR__.'/gen';
        require_once __DIR__.'/lib/Thrift/ClassLoader/ThriftClassLoader.php';
        $this->loader = new \Thrift\ClassLoader\ThriftClassLoader();
        $this->loader->registerNamespace('Thrift', __DIR__.'/lib');
        
        $dirs = scandir($genDir);
        foreach($dirs as $d)
        {
            if($d=='.' || $d=='..') continue;
            $this->loader->registerDefinition($d, $genDir);
        }
        
        $this->loader->register();
        
        foreach($this->serviceConfig as $name=>$config)
        {
            $this->services[$name] = $name;
        }
    }

    public function __get($name)
    {
        if(isset($this->services[$name]))
        {
            if(is_string($this->services[$name]))
            {
                $config = $this->serviceConfig[$name];
                $configName = array('sendTimeout', 'recvTimeout', 'serverHost', 'serverPort', 'dirName','maxConnectTimes','className');
                foreach($configName as $cn)
                {
                    if(empty($config[$cn]))
                    {
                        $config[$cn] = $this->{$cn};
                    }
                }
            
                for($i=0;$i<$config['maxConnectTimes'];$i++){
                    try{
                        $transport = new \Thrift\Transport\TSocket($config['serverHost'], $config['serverPort']);
                        $transport->setSendTimeout($config['sendTimeout'] * 1000);
                        $transport->setRecvTimeout($config['recvTimeout'] * 1000);
                        $transport->open();

                        if( $transport->isOpen() ){
                            $protocol = new \Thrift\Protocol\TBinaryProtocol(new \Thrift\Transport\TBufferedTransport($transport));
                            $class = $config['dirName'].'\\'.$config['className'].'Client';
                            $this->services[$name] = new $class($protocol);
                            break;
                        } 
                        
                    }catch(\Thrift\Exception\TException $e){
                        //log 
                        if($i == $config['maxConnectTimes']-1){
                            throw $e;
                        }
                        //接口异常警告
//                        if(YII_ENV == 'prod') {
//                            $content = array("content" => \Yii::$app->params['interfaceRemindMessage'] . "接口连接异常警告(请检查连接配置是否正确或接口是否已正常启动):[interfaceName: {$config['className']}, host: {$config['serverHost']}, port: {$config['serverPort']}']");
//                            G::curl_post(\Yii::$app->params['interfaceRemindUrl'], array("msgtype" => "text", "text" => $content));
//                        }
                    }
                }
            }
            return $this->services[$name];
        }
        else
        {
            throw new Exception('Service Not Defined');
        }
    }

    public function sendSMS()
    {
        $msgContent = new \MsgCenter\Sms();
        $msgContent->phone = '18575699392';
        $msgContent->code =  (int)102;
        $keys = array_keys(['name'=>'111']);
        array_walk($keys,function(&$value, $key){
            $value = 'params['.$value.']';
        });

        $msgContent->paras = json_encode(array_combine($keys, array_values(['name'=>'111'])), JSON_FORCE_OBJECT);

        $this->MsgThriftSmsService->sendSms($msgContent);
    }
}


