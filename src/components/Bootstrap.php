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
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

class Bootstrap implements BootstrapInterface
{
    protected static $_hooks = [];

    protected static $_events = [];

    /**
     * Bootstrap method to be called during application bootstrap stage.
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {
        if (!self::checkInit($app)) {
            return $app;
        }

        Hook::listen('plugins_init_begin');

        // 绑定插件信息
        $plugins = Common::getCache(null, 'plugins');
        if ($plugins && !YII_DEBUG) {
            foreach ($plugins as $info) {
                if ($info['state']) {
                    self::pluginsBind($info);
                }
            }
        } else {
            $files = FileHelper::findFiles(\Yii::getAlias(Common::$_pluginConfig['pluginRoot']), ['only' => ['/*/info.ini']]);
            foreach ($files as $file) {
                $info = Common::parse($file);
                Common::setCache($info['name'], $info, 'plugins');
                if ($info['state']) {
                    self::pluginsBind($info);
                }
            }
        }

        // 加载插件路由
        Common::initPluginsUrlRules();

        // 加载插件语义
        Common::initPluginsMessages();

        // 导入所有事件
        \Yii::createObject([
            'class' => 'zyh\plugins\components\EventManager',
            'events' => self::$_events
        ]);

        /**
         * 注册一个插件管理后台路由
         */
        $app->getUrlManager()->addRules([
            "plugins/<a:\w+>" => "plugins/plugins/<a>",
        ]);

        Hook::listen('plugins_init_end');

        // 导入所有钩子
        Hook::import(self::$_hooks);

    }

    /**
     * 检测插件使用前
     * @param $app
     * @throws Exception
     * @throws InvalidConfigException
     */
    public static function checkInit($app)
    {
        if ((Common::$_pluginConfig = ArrayHelper::getValue($app->modules, 'plugins')) == null) {
            return false;
        }

        if (!\Yii::$app->get('cache', false)) {
            throw new InvalidConfigException("Unknown component ID: cache");
        }

        if (!isset(Common::$_pluginConfig['pluginRoot'])) {
            throw new Exception("Unknown Plugins Module property: pluginRoot");
        }

        if (!isset(Common::$_pluginConfig['pluginNamespace'])) {
            throw new Exception("Unknown Plugins Module property: pluginNamespace");
        }

        return true;
    }

    /**
     * 插件绑定事件和钩子
     * @param
     * @return array|null
     */
    public static function pluginsBind($info)
    {
        $classNamespace = Common::$_pluginConfig['pluginNamespace'] . '\\' . $info['name'] . '\\' . Common::parseName($info['name'], 1);
        if (class_exists($classNamespace)) {
            $class = new $classNamespace();
            if ($class instanceof Plugin) {

                // 导入插件配置
                Common::getPluginConfig($info['name']);

                // 绑定钩子
                if ($class->hooks()) {
                    foreach ($class->hooks() as $name => $value) {
                        self::$_hooks[$name] = [$classNamespace, $value];
                    }
                }

                // 绑定事件
                if ($class->events()) {
                    foreach ($class->events() as $className => $events) {
                        foreach ($events as $eventName => $event) {
                            foreach ($event as $value) {
                                $value = is_array($value) ? $value : [$value];
                                if (count($value) > 1) {
                                    $data = isset($value[1]) ? $value[1] : null;
                                    $append = isset($value[2]) ? $value[2] : true;
                                    self::$_events[$className][$eventName][] = [[$classNamespace, $value[0]], $data, $append];
                                } else {
                                    self::$_events[$className][$eventName][] = [$classNamespace, $value[0]];
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}