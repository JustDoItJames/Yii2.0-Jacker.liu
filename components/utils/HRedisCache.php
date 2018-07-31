<?php
namespace app\components\utils;
use Yii;
use yii\base\Exception;

class HRedisCache
{
    private static $allCacheKey;
    private static $expireCacheTimeKey;
    private static $allCacheTime;
    private static $clearAllscret = 'clearallscret123456';


    public static function initHRedisCache($dfAllCacheKey = 'allDataKey',$dfAllCacheTime = 604800)
    {
        //初始化必要字段
        $urlKey = str_replace('/', '_', Yii::$app->request->getUrl());
        $urlKey = !empty($urlKey)?$urlKey:'index';
        self::$allCacheKey = !empty($dfAllCacheKey)?$dfAllCacheKey:$urlKey;//建议用模块+控制器+行为名称格式，以保证独立性
        self::$expireCacheTimeKey = self::$allCacheKey.'expireTime';
        self::$allCacheTime = ((int)$dfAllCacheTime > 0)?$dfAllCacheTime:604800;


        //处理清除缓存请求
        if(!empty($_GET['clearallscret'])){
            self::clearAllCache($_GET['clearallscret']);
        }elseif(!empty($_GET['clearrow'])){
            self::clearOneCache($_GET['clearrow']);
        }

        try{
            //缓存初始化
            $expireTime = Yii::$app->predis->get(self::$expireCacheTimeKey);
            if(empty($expireTime) || $expireTime < time()){
                //设置下次缓存过期时间
                $expireTime = time()+self::$allCacheTime;
                Yii::$app->predis->set(self::$expireCacheTimeKey,$expireTime);

                //初始化
                Yii::$app->predis->HSET(self::$allCacheKey,'init','init success');
                Yii::$app->predis->EXPIRE(self::$allCacheKey,self::$allCacheTime);
            }
        }catch(Exception $e){
            //写日志文件
//            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR,'initHRedisCache');
        }
    }


    /**
     * 总体缓存清除
     * @param  string $clearAllscret [description]
     * @return [type]              [description]
     */
    public static function clearAllCache($dfclearAllscret = ''){
        $clearStatus = false;

        try{
            if(!empty($dfclearAllscret) && ($dfclearAllscret == self::$clearAllscret)){
                $delTimeStatus = Yii::$app->predis->del(self::$expireCacheTimeKey);
                $clearStatus = Yii::$app->predis->del(self::$allCacheKey);
            }
        }catch(Exception $e){
            //写日志文件
//            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR,'clearAllCache');
        }

        if($clearStatus){
            //echo 'clear cache success';
        }else{
            //echo 'clear cache fail';
        }
    }

    /**
     * 清除单条缓存
     * @return [type] [description]
     */
    public static function clearOneCache($oneCacheKey){
        try{
            Yii::$app->predis->HSET(self::$allCacheKey,$oneCacheKey,'');
        }catch(Exception $e){
            //写日志文件
//            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR,'clearOneCache');
        }
    }


    /**
     * 缓存保存
     * @param  [type] $oneCacheKey [description]
     * @param  [type] $data        [description]
     * @return [type]              [description]
     */
    public static function saveCache($oneCacheKey,$data){
        try{
            return Yii::$app->predis->HSET(self::$allCacheKey,$oneCacheKey,serialize($data));
        }catch(Exception $e){
            //写日志文件
//            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR,'saveCache');
        }
    }


    /**
     * 读取缓存
     * @param  [type] $oneCacheKey [description]
     * @return [type]              [description]
     */
    public static function readOneCache($oneCacheKey){
        try{
            $cacheData = Yii::$app->predis->hget(self::$allCacheKey,$oneCacheKey);
            return !empty($cacheData)?unserialize($cacheData):'';
        }catch(Exception $e){
            //写日志文件
//            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR,'readOneCache');
        }
    }
}