<?php

namespace app\modules\common\controllers;

use app\components\controllers\ControllerV1;
use Yii;
use app\components\Mailer;
use app\models\thrift\UserCenterThriftService;
use app\models\Uploadm;
use yii\web\UploadedFile;
use yii\httpclient\Client;
use app\components\utils\HRedisCache;

/**
 * Default controller for the `common` module
 */
class DefaultController extends ControllerV1
{

    public function actions() {
        parent::actions();
        //增加行为控制
    }

    //"yii\filters\PageCache"类实现页面缓存
    /*
    public function behaviors()
    {
        return [
            [
                'class' => 'yii\filters\PageCache',
                'only' => ['index'],
                'duration' => 60,
//                'dependency' => [
//                    'class' => 'yii\caching\DbDependency',
//                    'sql' => 'SELECT COUNT(*) FROM post',
//                ],
//                'variations' => [
//                    \Yii::$app->language,
//                ],
            ],
        ];
    }
    */

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
//        \Yii::$app->redis->set('haha',111);
//        var_dump(\Yii::$app->redis->get('haha'));exit;
//        var_dump(Yii::$app->predis);exit;
//        \Yii::$app->predis->set('wan',123456789);
//        var_dump(\Yii::$app->predis->get('wan'));exit;

//            \Yii::$app->Restrict->assert('communityPost',562292241);
//        var_dump(\Yii::$app->Restrict->assert('communityPost',1));exit;
//        var_dump(\Yii::$app->ocs->get('djajdwqd'));exit;
//        var_dump(\Yii::$app->session);exit;
//        Yii::trace('再一次测试一下',__METHOD__);

        //别名：
//        var_dump(Yii::getAlias('@app'));
//        var_dump(Yii::getAlias('@foo'));exit;

        //事件处理方法:
//        $Mailer = new Mailer();
//        //事件处理器是静态类方法：
////        $Mailer->on(Mailer::EVENT_MESSAGE_SENT,['app\components\events\MessageEvent','handlers']);
//        //事件处理器是匿名函数：
//        $Mailer->on(Mailer::EVENT_MESSAGE_SENT,function ($event){
//            var_dump($event);
//        });//使用on方法，就是绑定一个事件处理器。
//        //$Mailer->off(Mailer::EVENT_MESSAGE_SENT);//移除全部的绑定时间处理器。off方法要在on方法之后执行才有效果
//        $Mailer->send('测试一下事件处理器');
        //end

        $thrift = new UserCenterThriftService();//thrift接口
        $res = $thrift->getUserBasicInfo('7605515443');var_dump($res);exit;

        return $this->render('index');
    }

    public function actionTest(){
        $model = new Uploadm();
        return $this->render('test',['model'=>$model]);
    }

    public function actionGetfiles(){
        $model = new Uploadm();
        if (Yii::$app->request->isPost) {
            $model->imgFile = UploadedFile::getInstances($model, 'imgFile');
            if ($model->upload()) {
                // 文件上传成功
                echo '上传成功';
            }
        }
    }

    //生产者：用于发送消息---rabbitMq
    public function actionProducter(){
        $data = 'hello rabbitMq'; //要发送的数据
        /** @var \app\components\amqp\AmqpWrapper $rabbitMq*/
        $rabbitMq = Yii::$app->rabbitMq;
        $msg = $rabbitMq->createMessage(json_encode($data, JSON_UNESCAPED_UNICODE));

        $rabbitMq->exchangeDeclare('test_exchange', 'direct', false, true, false)
            ->publish($msg, 'test_exchange', 'test_routing');
    }

    //模拟http post请求
    public function actionHttpClient(){
        $logData = ['msg' => '', 'request' => '', 'response' => ''];

        $client = new Client();
        $request = $client->createRequest()
                    ->setMethod('post')
                    ->setUrl('http://v.lwj.com/common/default/receive-data')
                    ->setData(['name' => 'jack.liu', 'email' => 'liuwenjie1993@gmail.com'])
                    ->setOptions(['timeout'=>6]);
        $boolean = false;
        for($i=1; $i<=3;$i++) {
            $response = $request->send();

            if($response->isOk) { //http请求发送成功
                $data = $response->getContent();
                $responseArr = json_decode($data);
//                var_dump($responseArr);exit;
                if($responseArr->status == 1){
                    $logData['msg'] = '数据名字传输正确!'.'第'.$i.'次尝试。';
                    $boolean= true;
                }else{
                    $logData['msg'] = '数据名字传输失败!'.'第'.$i.'次尝试。';
                }
            } else {
                $logData['msg'] = 'http请求发送失败!'.'第'.$i.'次尝试';
            }

            $logData['request'] = $request->toString();
            $logData['response'] = $response->toString();

            //输出日志信息
            echo date('Y-m-d H:i:s', time())."\n";
            var_dump($logData);
            echo "\n";

            //最多重试3次
            if($boolean){
                break;
            }else{
                sleep(3);
                continue;
            }
        }

        return true;

    }

    //接受http请求
    public function actionReceiveData(){
        $name = Yii::$app->request->post('name');
        if($name == 'jack.liu'){
            $status = 1;
            $message = 'Ok';
        }else{
            $status = 0;
            $message = 'the name is not true';
        }
        $email = Yii::$app->request->post('email');
        echo json_encode(['name'=>$name,'email'=>$email,'status'=>$status,'message'=>$message]);
    }

    //测试HRedisCache缓存组件
    public function actionTestCache(){
        $cacheKey = '562292241';
        HRedisCache::initHRedisCache('commonDefaultTestCache',200);
        $cacheData = HRedisCache::readOneCache($cacheKey);
        $flag = 0;
        if(empty($cacheData)){
            $data = 'test cache data';
            if(!empty($data)){
                $flag = HRedisCache::saveCache($cacheKey,$data);
            }
        }
        var_dump($flag);exit;
    }


}
