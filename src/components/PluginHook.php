<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/1/22
 * Time: 下午6:34
 */

namespace zyh\plugins\components;


use yii\base\Object;
use yii\helpers\FileHelper;

class PluginHook extends Object
{
    /**
     * 插件事件搜索目录
     * @var array
     */
    public $filesPath = [];

    /**
     * 初始化钩子事件
     * @var array
     */
    public $tags = [];


    public function run()
    {
        if (empty($this->filesPath)) {
            return [];
        }

        foreach ($this->filesPath as $key => $value) {
            $files = FileHelper::findFiles(\Yii::getAlias($key), ['only' => [$value]]);
            foreach ($files as $file) {
                $params = Common::parse($file);
//                Fun::setCache($params['name'], $params, 'addons');
                if (!$params['state']) {
                    continue;
                }
                $class = \Yii::$app->getModule($params['name']);
                if ($class instanceof Module) {
                    // 注册插件模块
//                    \Yii::$app->setModule($params['name'],$class::className());
                    $this->tags = !$class->tags ? $this->tags : ArrayHelper::merge($this->tags, $class->tags);
                }
            }
        }

        Hook::import($this->tags);

        // 初始化钩子事件
//        if ($this->tags) {
//            foreach ($this->tags as $name => $value) {
//                $methodName = Common::parseName($name, 1);
//                $value = is_array($value) ? array_unique($value) : [$value];
//                foreach ($value as $v) {
//                    Hook::import($this->tags);
////                    \Yii::$app->on($name, [$v, $methodName]);
//                }
//            }
//        }
    }

    /**
     * 设置基础配置信息
     * @param string $name 插件名
     * @param array $array
     * @return boolean
     * @throws Exception
     */
//    function setAddonInfo($name, $array)
//    {
//        $file = ADDON_PATH . $name . DIRECTORY_SEPARATOR . 'info.ini';
//        $addon = \Yii::$app->getModule($name);
//        $array = $addon->setInfo($name, $array);
//        $res = array();
//        foreach ($array as $key => $val) {
//            if (is_array($val)) {
//                $res[] = "[$key]";
//                foreach ($val as $skey => $sval)
//                    $res[] = "$skey = " . (is_numeric($sval) ? $sval : $sval);
//            } else
//                $res[] = "$key = " . (is_numeric($val) ? $val : $val);
//        }
//        if ($handle = fopen($file, 'w')) {
//            fwrite($handle, implode("\n", $res) . "\n");
//            fclose($handle);
//            //清空当前配置缓存
//            Config::set($name, NULL, 'addoninfo');
//        } else {
//            throw new \Exception("文件没有写入权限");
//        }
//        return true;
//    }


    /**
     * 获取插件的单例
     * @param $name
     * @return mixed|null
     */
//    function get_addon_instance($name)
//    {
//        static $_addons = [];
//        if (isset($_addons[$name])) {
//            return $_addons[$name];
//        }
//        $class = get_addon_class($name);
//        if (class_exists($class)) {
//            $_addons[$name] = new $class();
//            return $_addons[$name];
//        } else {
//            return null;
//        }
//    }

    /**
     * 把物理路径文件转换类
     * @param $file
     */
    public static function getClassName($file)
    {
        $pathInfo = pathinfo($file);
        $str = str_replace(\Yii::getAlias('@app'), 'app', $pathInfo['dirname'] . '/' . $pathInfo['filename']);
        $className = str_replace('/', '\\', $str);
        return $className;
    }
}