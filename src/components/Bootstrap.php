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
        $instance = Configs::instance();

        if($instance->pluginScope && isset($instance->pluginScope[$app->id])){
            return;
        }

        Hook::listen('plugins_init_begin');

        // 绑定插件信息
        $plugins = Common::getCache(null, 'plugins');
        if ($plugins && !YII_DEBUG) {
            foreach ($plugins as $info) {
                if ($info['state']) {
                    self::pluginsBind($info, $instance);
                }
            }
        } else {
            $files = FileHelper::findFiles(\Yii::getAlias($instance->pluginRoot), ['only' => ['/*/' . $instance->pluginFile]]);
            foreach ($files as $file) {
                $info = Common::parse($file);
                Common::setCache($info['name'], $info, 'plugins');
                if ($info['state']) {
                    self::pluginsBind($info, $instance);
                }
            }
        }

        // 加载插件路由规则
        $app->getUrlManager()->addRules([$instance->pluginUrlRule ?: ['class' => 'zyh\plugins\components\PluginUrlRule']]);

        // 加载插件语义
        self::initPluginsMessages($app);

        // 导入所有事件
        \Yii::createObject([
            'class' => 'zyh\plugins\components\EventManager',
            'events' => self::$_events
        ]);

        Hook::listen('plugins_init_end');

        // 导入所有钩子
        Hook::import(self::$_hooks);
    }

    /**
     * 插件绑定事件和钩子
     * @param $info
     * @param $instance
     */
    public static function pluginsBind($info, $instance)
    {
        $classNamespace = $instance->pluginNamespace . '\\' . $info['name'] . '\\' . Common::parseName($info['name'], 1);
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

    /**
     * 插件国际化语义初始化
     * @param string $name
     */
    public static function initPluginsMessages($app)
    {
        $plugins = Common::getCache('', 'plugins', []);
        foreach ($plugins as $key => $plugin) {
            if (empty($plugin)) {
                continue;
            }
            if (!isset($app->getI18n()->translations[$key . '*'])) {
                $path = \Yii::getAlias(Configs::instance()->pluginRoot . DIRECTORY_SEPARATOR . $key);
                $fileMap = Common::getCache($key . '.messages', 'plugins');
                if (!$fileMap) {
                    $files = FileHelper::findFiles($path, ['only' => ['controllers/*.php']]);
                    $fileMap = [
                        $key => $key . ".php",
                    ];
                    foreach ($files as $file) {
                        $fileInfo = pathinfo($file);
                        $controllerName = strtolower(str_replace('Controller', '', $fileInfo['filename']));
                        $uri = ($key == $controllerName ? $key : $key . DIRECTORY_SEPARATOR . $controllerName);
                        $fileMap[$uri] = $controllerName . '.php';
                    }
                    Common::setCache($key . '.messages', $fileMap, 'plugins');
                }
                $app->getI18n()->translations[$key . '*'] = [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => $path . DIRECTORY_SEPARATOR . 'messages',
                    'fileMap' => $fileMap,
                ];
            }
        }
//        var_dump($app->getI18n()->translations);
//        die;
    }
}