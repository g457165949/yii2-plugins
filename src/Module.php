<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/1/18
 * Time: 上午10:38
 */

namespace zyh\plugins;


use zyh\plugins\components\Common;
use zyh\plugins\components\Configs;

class Module extends \yii\base\Module
{
    /**
     * 插件名称
     * @var string
     */
    public $pluginId = '';


    public function init()
    {
        parent::init();
        $route = \Yii::$app->requestedRoute;
        $array = explode("/", trim($route, "/"));

        // 判断是否是已安装的插件路由
        if ($array[0] != $array[1] && Common::getPluginClass($array[1])) {
            $this->pluginId = $array[1];
            $this->controllerNamespace = Configs::instance()->pluginNamespace . '\\' . strtolower($array[1]);
            $this->setPluginViewPath();
        }
        \Yii::setAlias('@module',dirname(__FILE__));
        $this->registerTranslations();
    }

    public function registerTranslations()
    {
        if (!isset(\Yii::$app->i18n->translations[$this->id . '*'])) {
            \Yii::$app->i18n->translations[$this->id . '*'] = [
                'class' => 'yii\i18n\PhpMessageSource',
                'basePath' => '@module/messages',
                'fileMap' => [
                    'plugins/plugins' => 'plugins.php'
                ],
                'on missingTranslation' => ['zyh\plugins\components\TranslationEventHandler', 'handleMissingTranslation']
            ];
        }
    }

    /**
     * 创建插件的controller
     * @param string $route
     * @return array|bool
     */
    public function createController($route)
    {
        if ($this->pluginId) {
            $this->controllerNamespace = $this->controllerNamespace . '\\controllers';
            $route = str_replace($this->pluginId . "/", '', $route, $i);
            $route = $i == 2 ? $this->pluginId . "/" . $route : $route;
        }
        return parent::createController($route);
    }

    /**
     * 设置插件的模板路径
     */
    public function setPluginViewPath()
    {
        $path = \Yii::getAlias(rtrim(Configs::instance()->pluginRoot, '/')) . DIRECTORY_SEPARATOR . $this->pluginId . DIRECTORY_SEPARATOR . 'views';
        if (is_dir($path)) {
            $this->setViewPath($path);
        }
    }
}