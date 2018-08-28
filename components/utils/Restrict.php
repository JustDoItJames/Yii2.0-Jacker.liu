<?php
/**
 *安全限制组件
 *@author jacket.liu<liuwenjie1993@gmail.com>
 *@version 1.0
 */

/**
 *该组件实现了常见的错误次数限制(密码错误次数，同ip访问次数....)
 *依赖组件:redis
 *第一版本功能：
 * 1.根据唯一key做计数限制（用户名，手机号）
 * 2.同ip限制次数
 * 3.refer限制
 *
 *使用方法:
 * 1.组件配置方法：
 *      'Restrict'=>array(
 *          'class'=>'application.components.util.Restrict',
 *          'cacheID'=>'redis',
 *          'restricts'=>array(         //限制配置，键值数组，key为限制名称
 *              'password'=>array(      //名称为password
 *                  'ip'=>0             //同一ip限制访问次数，0表示无限制
 *                  'custom'=>2,        //自定义key限制次数，比如同一用户名的密码错误次数
 *                  'howlong'=>3600,    //限制时间，表示一小时之内，最多达到上述ip，custom指定次数
 *                  'refer'=>''         //限制访问的http refer头，值为正则表达式，空值或空串为不限制
 *                ),
 *           )
 *       )
 *2.组件调用方法(以登录为例)：
 *  2.1
 *      //进行登录操作之前
 *      if(!Yii::$app->Restrict->assert('password','18682174968')) {   //表示使用password配置，传入唯一的key为用户手机号
 *          //处理超过限制阀值
 *      }
 *
 *      //登录成功之后
 *      Yii::$app->Restrict->reset('password','18682174968');      //登录成功，清楚之前限制计数
 */
namespace app\components\utils;

use Yii;
use yii\base\Component;
use yii\log\Logger;

class Restrict extends Component
{

    //默认
    CONST ONEDAY = 86400;
    /**
     *@var 计数使用的cache组件
     */
    public $cacheID = 'predis';

    /**
     *@var 计数key的公共前缀
     */
    public $prefix = 'Restrict';
    /**
     *@var 限制配置
     */
    private $_restricts = array();
    private $wr;//liuwenjie：w读加 r只读


    /**
     * @description 获取key
     * @param $r
     * @param $key
     * @param string $type
     * @return string
     */
    private function getKey($r,$key,$type='CUSTOM'){
        return $this->prefix.$r.$type.'_'.$key;
    }

    /**
     * @description 判断custom次数是否超过限制，如果超过，则返回true。否则为false
     * @param $r
     * @param $key
     * @return bool|mixed
     * @throws \yii\base\Exception
     */
    private function isUserNumLimit($r, $key)
    {
        if(is_callable($key)) {
            $key = array($key,array());
        }
        if(is_array($key) && !empty($key) && is_callable($key[0])) {
            list($f,$p) = $key;
            return call_user_func_array($f, $p);
        }

        if(is_array($key) || is_object($key) || is_resource($key)) {
            return false;
        }

        if(!isset($this->_restricts[$r]['custom']) || $this->_restricts[$r]['custom'] <= 0) {
            return false;
        }

        //$key = $this->getKey($r,$key);所有人key相同
        $ip = Yii::$app->request->userIP;
        $key = $this->getKey($r,$key,$ip);//生成key
        $current = $this->inscrement($r,$key);
        return $current['times'] >= $this->_restricts[$r]['custom']; //访问次数超过custom的限制次数，就返回true。否则为false
    }

    /**
     * @description 判断ip次数是否超过限制，如果超过，则返回true。否则为false
     * @param $r
     * @return bool
     * @throws \yii\base\Exception
     */
    private function isIpLimit($r)
    {
        if(!isset($this->_restricts[$r])) {
            return false;
        }

        $rt = $this->_restricts[$r];
        if(empty($rt) || $rt['ip'] <= 0) {
            return false;
        }
        //获取当前用户ip地址
        $ip = Yii::$app->request->userIP;

        //判断当前用户ip是否在白名单之内
        if (isset($rt['ipBlankList']) && is_array($rt['ipBlankList'])) {
            if(in_array($ip, $rt['ipBlankList'])){
                return false;
            }
        }

        //判断当前用户ip地址是否在黑名单中
        if (isset($rt['ipBlackList']) && is_array($rt['ipBlackList'])) {
            if(in_array($ip, $rt['ipBlackList'])){
                return true;
            }
        }

        $key = $this->getKey($r,$ip,'IP');
        $current = $this->inscrement($r,$key);
        return $current['times'] >= $rt['ip'];//次数超过ip的限制次数，就返回true。否则为false
    }

    /**
     * @description 正则匹配refer。如果refer匹配不成功，就返回true。否则返回false
     * @param $r
     * @return bool
     */
    private function isReferBad($r){
        if(!isset($this->_restricts[$r])) {
            return false;
        }
        $rt = $this->_restricts[$r];
        if(empty($rt['refer'])) {
            return false;
        }
        //获取当前用户的refer
        $refer = Yii::$app->request->referrer;

        return !preg_match($rt['refer'], $refer); //refer不匹配的话，就返回true。否则返回false
    }


    private function executeHandles($r,$callback=array()){
        $handles = array();
        if(isset($this->_restricts[$r]['handles'])) {
            foreach($handles as $h) {
                if(is_callable($h)) {
                    $h = array($h,array());
                }
                if(!is_array($h) || empty($h)) {
                    continue;
                }

                if(!is_callable($h[0])) {
                    continue;
                }
                array_push($handles,$h);
            }
        }
        if(is_callable($callback)) {
            $callback = array($callback,array());
        }
        if(!empty($callback) && is_callable($callback[0])) {
            array_push($handles,$callback);
        }
        foreach($handles as $h) {
            list($f,$p) = $h;
            call_user_func_array($f, $p);
        }
    }
    private function inscrement($r, $key)
    {
        if (!isset($this->_restricts[$r])) {
            throw new \yii\base\Exception("the restrict {$r} config is not found");
        }
        $rt = $this->_restricts[$r];
        if (!($data = Yii::$app->{$this->cacheID}->get($key))) {
            $data = array('times' => 0, 'timestamp' => time());
        }else{
            $data = json_decode($data, true);
        }

        if ('w' == $this->wr){
            $timeInterval = isset($rt['howlong']) ? $rt['howlong'] : self::ONEDAY;
            $timeInterval = $timeInterval - (time() - $data['timestamp']);
            $timeInterval = abs($timeInterval);
            if (empty($timeInterval)) {
                return $data;
            }
            $returnData = $data;
            $data['times'] = isset($data['times']) ? ($data['times'] + 1) : 1;//次数+1
            Yii::$app->{$this->cacheID}->setex($key, $timeInterval, json_encode($data));//实时更新key的过期时间
            return $returnData;
        }else{
            return $data;
        }
    }


    public function init()
    {
        //检查缓存组件是否存在
        try {
            Yii::$app->{$this->cacheID};
        } catch(\yii\base\Exception $e) {
            throw new \yii\base\Exception("the Restrict components need the cache components[".$this->cacheID."] to be loaded!");
        }
    }

    public function setRestricts($rt)
    {
        foreach($rt as $k=>$r) {
            //ip限制数据
            if(!isset($r['ip'])) {
                $r['ip'] = 0;
            }
            //refer限制
            if(!isset($r['refer'])) {
                $r['refer'] = '';
            }
            $this->_restricts[$k] = $r;
        }
    }

    public function getExpireTime($r)
    {
        if(!isset($this->_restricts[$r])) {
            return 0;
        }

        $rt = $this->_restricts[$r];
        if(!isset($rt['howlong'])) {
            return 0;
        }

        return (int)$rt['howlong'];

    }
    public function assert($r,$key = null,$callback=array(), $wr='w')
    {
        $this->wr = $wr;
        if(!isset($this->_restricts[$r])) {
            throw new \yii\base\Exception("the restricts {$r} is not defined!");
        }
        do {
            if($this->isIpLimit($r)) {
                break;
            }

            if($this->isReferBad($r)) {
                break;
            }

            if($this->isUserNumLimit($r,$key)) {
                break;
            }
            return true;
        } while(false);

        if(is_array($callback) && !empty($callback)){
            $this->executeHandles($r,$callback);
        }
        return false;
    }

    public function reset($r,$key=''){
        if(!isset($this->_restricts[$r])) {
            return true;
        }

        $rt = $this->_restricts[$r];
        $keys = array();
        if(isset($rt['ip'])) {
            $ip = Yii::$app->request->userIP;
            array_push($keys,$this->getKey($r,$ip,'IP'));
        }

        if(isset($rt['custom'])) {
            array_push($keys,$this->getKey($r,$key));
        }

        foreach($keys as $k) {
            Yii::$app->{$this->cacheID}->delete($k);
        }
    }

    /**
     * todo 如果是刷接口，则加入黑名单
     * @author jacket.liu
     * @param array  param['restrict_config_name'] 配置名  param['restrict_config_name'] 自定义key  用法和 assert 方法的传参相同
     * @return mixed
     */
    public function newRestrict($param=array())
    {
        $restrict_config_name = isset($param['restrict_config_name'])?$param['restrict_config_name']:'';
        $restrict_common_name = isset($param['restrict_common_name'])?$param['restrict_common_name']:'';
        if(!$restrict_config_name || !$restrict_common_name){
            $userState = null;
        }else{
            $userState = $this->getUserSecurityIdentify();
            if($userState == 'regular_user' && !$this->assert($restrict_config_name, $restrict_common_name)){
                $protect_intence = new Protect();
                $clientIp  = Yii::$app->request->userHostAddress;
                $protect_intence->addBlackList($clientIp);
            }
        }
        return $userState;
    }

    //是否是白名单、黑名单、正常用户
    public function getUserSecurityIdentify()
    {
        $protect_intence = new Protect();
        $clientIp  = Yii::$app->request->userHostAddress;
        if($protect_intence->isWhite($clientIp)){
            return 'white_user';

        }else{
            if($protect_intence->isBlack($clientIp)){
                return 'black_user';

            }else{
                return 'regular_user';
            }
        }
    }
}

class Protect
{
    public $reids_key = 'THE_BLACK_LIST';
    public $expire    = 86400;
    private static $_instance = null;

    //只能实例化一次
    public function __construct()
    {
        if(isset(self::$_instance) && is_null(self::$_instance)) {
            self::$_instance = new self ();
        }
        return self::$_instance;
    }

    //禁止克隆
    private function __clone(){
        throw new \yii\base\Exception('Clone is not allow!');
    }

    //是否是白名单用户
    public function isWhite($ip){
        $white_list = array('');
        return in_array($ip, $white_list)? true : false;
    }


    //是否是黑名单用户
    public function isBlack($ip)
    {
        $black_ip = null;
        $key = $this->reids_key . "_" .$ip;
        if(Yii::$app->predis->exists($key)){
            $black_ip = Yii::$app->predis->get($key);
        }
        return empty($black_ip) ? false : true;
    }

    //加入黑名单
    public function addBlackList($ip)
    {
        $key = $this->reids_key . "_" .$ip;
        try{
            Yii::$app->predis->setex($key, $this->expire, $ip);
        }catch (\Predis\PredisException $e)
        {
            Yii::getLogger()->log($e->getMessage(), logger::LEVEL_ERROR, 'restrict_add_black_list');
        }
    }
}


