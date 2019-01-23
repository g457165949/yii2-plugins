<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/1/22
 * Time: 下午6:47
 */

namespace zyh\plugins\components;


use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

class HookBootstrap implements BootstrapInterface
{
    protected static $hooks = [];

    protected static $pluginsDir = [];

    /**
     * Bootstrap method to be called during application bootstrap stage.
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {
        self::hooksBind($app);
    }

    public static function hooksBind($app)
    {
        if(empty(self::$pluginsDir)){
            throw new Exception('The plugin directory cannot be empty');
        }

        if (self::$hooks) {
            return self::$hooks;
        }

        foreach (self::$pluginsDir as $key => $value) {
            $files = FileHelper::findFiles(\Yii::getAlias($key), ['only' => [$value]]);
            foreach ($files as $file) {
                $params = Common::parse($file);
//                Fun::setCache($params['name'],$params,'addons');
                if (!$params['state']) {
                    continue;
                }
                $class = \Yii::$app->getModule($params['name']);
                if ($class instanceof Plugin) {
                    // 注册插件模块
//                    \Yii::$app->setModule($params['name'],$class::className());
                    self::$hooks[] = !$class->tags ? self::$hooks : ArrayHelper::merge(self::$hooks, $class->tags);
                }
            }
        }

        Hook::import(self::$hooks);
    }
}