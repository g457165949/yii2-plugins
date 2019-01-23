<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/1/18
 * Time: 上午10:38
 */

namespace zyh\plugins;


class Module extends \yii\base\Module 
{
    public $realRoute = '';

    public $pluginId = '';
    /**
     * 插件路径
     * @var string
     */
    public $pluginRoot = '@app/plugins';

    /**
     * 插件命名
     * @var string
     */
    public $pluginNamespace = 'app\plugins';

    public function init()
    {
        parent::init();
        $route = \Yii::$app->requestedRoute;
        $array = explode("/", trim($route, "/"));
        if ($array['0'] == $this->id) {
            $pluginId = isset($array[1]) ? explode(":", $array[1]) : $array[1];
            $namespace = join("\\", $pluginId);
            $this->controllerNamespace = 'app\plugins\\' . strtolower($namespace);
            $this->pluginId = $pluginId[0];
            if (count($pluginId) > 1) {
                unset($array[0]);
                unset($array[1]);
                $this->realRoute = join("/", $array);
            }
        }
    }

    public function createController($route)
    {
        if (!$this->realRoute) {
            $array = explode("/", $route);
            //兼容 plugins/menu/MenuController.php的情况
            if (count($array) >= 3 && $array[0] == $array[1]) {
                $file = $this->pluginNamespace . "/{$array[0]}/" . ucfirst($array[0]) . "Controller.php";


                if (is_file($file)) {
                    array_shift($array);
                    $route = join("/", $array);
                }
            }
        }
        $controller = parent::createController($this->realRoute ? $this->realRoute : $route);
        if (!$controller) {
            $this->controllerNamespace = $this->controllerNamespace . '\\controllers';

            $route = str_replace($this->pluginId . "/", '', $route, $i);
            $route = $i == 2 ? $this->pluginId . "/" . $route : $route;

            $controller = parent::createController($this->realRoute ? $this->realRoute : $route);
        }
        return $controller;
    }

    public function beforeAction($action)
    {
        $this->setPluginViewPath();
        if (!parent::beforeAction($action)) {
            return false;
        }
        return true; // or false to not run the action
    }

    public function setPluginViewPath()
    {
        $path = \Yii::getAlias(rtrim($this->pluginRoot,'/')) . DIRECTORY_SEPARATOR . $this->pluginId . DIRECTORY_SEPARATOR . 'views';
        if (is_dir($path)) {
            $this->setViewPath($path);
        }
    }
}