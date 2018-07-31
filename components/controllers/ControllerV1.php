<?php
/**
 * Created by PhpStorm.
 * User: liuwenjie
 * Date: 2017/12/9
 * Time: 10:34
 * desc: 基类（登录信息，布局选择，核心js、css加载）
 */
namespace app\components\controllers;

use yii;
use yii\base\Controller;
use yii\base\InlineAction;
use app\components\utils\RequireJsCssLoader;


class ControllerV1 extends Controller
{

    public function init()
    {
        parent::init();
        /*//初始化布局
        $this->layout = '@app/views/layouts/commonV1.php';
        //初始化view 对象
        if (!\yii::$app->request->isAjax) {
            RequireJsCssLoader::loadCommon($this->view);
        }
        */
    }

    /**
     * @author zhengshipeng
     * @param string $id
     * @return InlineAction|null
     * @description 重写create 支持驼峰使用
     */
    public function createAction($id)
    {
        if ($id === '') {
            $id = $this->defaultAction;
        }
        $actionMap = $this->actions();
        if (isset($actionMap[$id])) {
            return Yii::createObject($actionMap[$id], [$id, $this]);
        } elseif (preg_match('/^[a-z0-9\\-_]+$/', $id) && strpos($id, '--') === false && trim($id, '-') === $id) {
            $methodName = 'action' . str_replace(' ', '', ucwords(implode(' ', explode('-', $id))));
        } else {
            $methodName = 'action' . $id;
        }
        if (method_exists($this, $methodName)) {
            $method = new \ReflectionMethod($this, $methodName);
            if ($method->isPublic() && $method->getName() === $methodName) {
                return new InlineAction($id, $this, $methodName);
            }
        }
        return null;
    }

}