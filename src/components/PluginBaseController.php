<?php
/**
 * PluginBaseController
 * 所有的插件都要继承此controller
 * @author xiongchuan <xiongchuan86@gmail.com>
 */
namespace zyh\plugins\components;

use app\components\KitEvent;
use yii\base\Event;
use yii\web\Controller;

class PluginBaseController extends Controller
{
    public function init()
    {
        parent::init();
    }

    public function actions()
    {
        return Hook::listen('hook_plugin_actions',parent::actions());
    }

    public function getViewPath()
    {
        return $this->module->getViewPath();
    }

    public function renderView($view, $params = [])
    {
        $realView = $this->id . '/' . $view;
        return $this->render($realView, $params);
    }
}