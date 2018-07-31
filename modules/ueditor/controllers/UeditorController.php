<?php

/**
 * 该控制器处理UEditor发出的相应请求
 * Created by PhpStorm.
 * User: michael.shi
 * Date: 2017/11/24
 * Time: 13:44
 */
namespace app\modules\ueditor\controllers;
use yii\base\Controller;
use Yii;
use app\modules\ueditor\components\AliUplaoder;
use app\service\CommunityService;

class UeditorController extends Controller
{
    //UEditor的配置，对应的配置文件在当前模块下的/config/ueditorConfig.json,所有与UEditor相关的配置请前往该文件配置
    protected $config;

    //get请求中的action参数
    protected $actionParam;
    
    public function init(){
        $this->config = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents(__DIR__."/../config/ueditorConfig.json")), true);
        $this->actionParam = $_GET['action'];
    }

    /**
     * UEditor请求的入口
     * @author shiweihua
     * @date 2017/11/24
     */
    public function actionIndex(){
        switch ($this->actionParam) {
            case 'config': //获取UEditor配置
                $result =  json_encode($this->config);
                break;
            case 'uploadimage'://上传图片
            case 'uploadscrawl'://上传涂鸦
            case 'uploadvideo'://上传视频
            case 'uploadfile'://上传文件
                $result = $this->upload();
                break;
            case 'listimage'://列出图片
                $result = $this->listFile();
                break;
            case 'listfile'://列出文件
                $result = $this->listFile();
                break;
            case 'catchimage'://抓取远程文件
                $result = $this->crawler();
                break;
            default:
                $result = json_encode(array(
                    'state'=> '请求地址出错'
                ));
                break;
        }

        //输出结果
        if (isset($_GET["callback"])) {
            if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
                echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
            } else {
                echo json_encode(array(
                    'state'=> 'callback参数不合法'
                ));
            }
        } else {
            echo $result;
        }

        Yii::$app->end();
    }

    /**
     * 上传文件
     * @author shiweihua
     * @date 2017/11/24
     * @return string Json字符串，由数组encode得到，数组包含上传文件所对应的各个参数,数组结构如下
     * array(
     *     "state" => "",          //上传状态，上传成功时必须返回"SUCCESS"
     *     "url" => "",            //返回的地址
     *     "title" => "",          //新文件名
     *     "original" => "",       //原始文件名
     *     "type" => ""            //文件类型
     *     "size" => "",           //文件大小
     * )
     */
    protected function upload(){

        //获取上传的相关配置
        list($fieldName, $config, $type) = $this->getUploadConfig();

        //生成上传实例对象并完成上传
        $uploadInstance = new AliUplaoder($fieldName, $config, $type);

        //返回结果
        return json_encode($uploadInstance->getFileInfo());
    }

    /**
     * 列出文件
     * @author shiweihua
     * @date 2017/11/24
     */
    protected function listFile(){
    }

    /**
     * 抓取远程文件
     * @author shiweihua
     * @date 2017/11/24
     */
    protected function crawler(){
    }

    /**
     * 上传配置
     * @author shiweihua
     * @date 2017/11/24
     * @return array
     */
    protected function getUploadConfig(){
        $type = "upload";
        switch (htmlspecialchars($_GET['action'])) {
            case 'uploadimage'://上传图片
                $config = array(
                    "pathFormat" => $this->config['imagePathFormat'],
                    "maxSize" => $this->config['imageMaxSize'],
                    "allowFiles" => $this->config['imageAllowFiles']
                );
                $fieldName = $this->config['imageFieldName'];
                break;
            case 'uploadscrawl'://上传涂鸦
                $config = array(
                    "pathFormat" => $this->config['scrawlPathFormat'],
                    "maxSize" => $this->config['scrawlMaxSize'],
                    "allowFiles" => $this->config['scrawlAllowFiles'],
                    "oriName" => "scrawl.png"
                );
                $fieldName = $this->config['scrawlFieldName'];
                $type = "base64";
                break;
            case 'uploadvideo'://上传视频
                $config = array(
                    "pathFormat" => $this->config['videoPathFormat'],
                    "maxSize" => $this->config['videoMaxSize'],
                    "allowFiles" => $this->config['videoAllowFiles']
                );
                $fieldName = $this->config['videoFieldName'];
                break;
            case 'uploadfile'://上传文件
            default:
                $config = array(
                    "pathFormat" => $this->config['filePathFormat'],
                    "maxSize" => $this->config['fileMaxSize'],
                    "allowFiles" => $this->config['fileAllowFiles']
                );
                $fieldName = $this->config['fileFieldName'];
                break;
        }
        return [$fieldName, $config, $type];
    }

}