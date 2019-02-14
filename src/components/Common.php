<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/1/18
 * Time: 下午2:43
 */

namespace zyh\plugins\components;


use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

class Common
{
    /**
     * 插件module配置信息
     * @var array
     */
    public static $_pluginConfig = [];

    /**
     * 插件语义翻译
     * @param $msg
     * @param $params
     * @return string
     */
    public static function t($msg, $params = [])
    {
        $category = str_replace('/' . \Yii::$app->controller->action->id, '', \Yii::$app->request->pathInfo);
        return \Yii::t($category, $msg, $params);
    }

    /**
     * AJAX 返回错误信息
     * @param $msg
     * @param int $code
     * @return array
     */
    public static function error($msg, $code = -1)
    {
        return ['msg' => $msg, 'code' => $code];
    }

    /**
     * AJAX 返回错误信息
     * @param $data
     * @param int $code
     * @return array
     */
    public static function success($data, $code = 0)
    {
        return ['data' => $data, 'code' => $code];
    }

    /**
     * 获取插件目录
     * @param $pluginName
     * @return bool|string
     */
    public static function pluginPath($pluginName = '')
    {
        $path = \Yii::getAlias(self::$_pluginConfig['pluginRoot']);
        return $pluginName ? $path . DIRECTORY_SEPARATOR . $pluginName : $path;
    }

    /**
     * 获取缓存
     * @param $key
     * @param null $default
     * @param string $category
     * @return mixed|null
     */
    public static function getCache($key = null, $category, $default = null)
    {
        $config = \Yii::$app->cache->get($category);
        return $config ? ($key ? ArrayHelper::getValue($category, $key, $default) : $config) : $default;
    }

    /**
     * 设置缓存
     * @param $key
     * @param $value
     * @param string $category
     * @return bool
     */
    public static function setCache($key, $value, $category, $duration = null)
    {
        $config = self::getCache('', $category);
        if ($key) {
            ArrayHelper::setValue($config, $key, $value);
        } else {
            $config = $value;
        }
        return \Yii::$app->cache->set($category, $config, $duration);
    }

    /**
     * 删除缓存
     * @param $key
     * @param $category
     */
    public static function delCache($key, $category)
    {
        if ($key) {
            $config = self::getCache('', $category);
            ArrayHelper::remove($config, $key);
            return \Yii::$app->cache->set($category, $config);
        } else {
            return \Yii::$app->cache->delete($category);
        }
    }

    /**
     * 清除插件所有缓存
     */
    public static function delAllCache()
    {
        // 插件配置缓存
        Common::delCache('', 'plugins');
        // 插件路由配置缓存
        Common::delCache('', 'pluginsUrlRules');
    }

    /**
     * 解析配置文件或内容
     * @access public
     * @param  string $config 配置文件路径或内容
     * @param  string $type 配置解析类型
     * @return mixed
     */
    public static function parse($config, $type = '')
    {
        if (empty($type)) $type = pathinfo($config, PATHINFO_EXTENSION);

        $class = false !== strpos($type, '\\') ?
            $type :
            '\\zyh\\plugins\\components\\driver\\' . ucwords($type);

        return (new $class())->parse($config);
    }

    /**
     * 字符串命名风格转换
     * type 0 将 Java 风格转换为 C 的风格 1 将 C 风格转换为 Java 的风格
     * @access public
     * @param  string $name 字符串
     * @param  integer $type 转换类型
     * @param  bool $ucfirst 首字母是否大写（驼峰规则）
     * @return string
     */
    public static function parseName($name, $type = 0, $ucfirst = true)
    {
        if ($type) {
            $name = preg_replace_callback('/_([a-zA-Z])/', function ($match) {
                return strtoupper($match[1]);
            }, $name);

            return $ucfirst ? ucfirst($name) : lcfirst($name);
        }

        return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
    }


    /**
     * 获取插件类的类名
     * @param string $name 插件名
     * @param string $type 返回命名空间类型
     * @param string $class 当前类名
     * @return string
     */
    public static function getPluginClass($name, $type = 'hook', $class = null)
    {
        $name = self::parseName($name);
        // 处理多级控制器情况
        if (!is_null($class) && strpos($class, '.')) {
            $class = explode('.', $class);

            $class[count($class) - 1] = self::parseName(end($class), 1);
            $class = implode('\\', $class);
        } else {
            $class = self::parseName(is_null($class) ? $name : $class, 1);
        }

        switch ($type) {
            case 'controller':
                $namespace = self::$_pluginConfig['pluginNamespace'] . $name . "\\controller\\" . $class;
                break;
            default:
                $namespace = self::$_pluginConfig['pluginNamespace'] . $name . "\\" . $class;
        }
        return class_exists($namespace) ? $namespace : '';
    }

    /**
     * 获取插件信息
     * @param $name
     * @return array
     */
    public static function getPluginInfo($name)
    {
        $plugin = self::getPluginInstance($name);
        if (!$plugin) {
            return [];
        }
        return $plugin->getInfo($name);
    }

    /**
     * 获取插件类的配置值值
     * @param string $name 插件名
     * @return array
     */
    public static function getPluginConfig($name)
    {
        $plugin = self::getPluginInstance($name);
        if (!$plugin) {
            return [];
        }
        return $plugin->getConfig($name);
    }

    /**
     * 写入配置文件
     * @param string $name 插件名
     * @param array $config 配置数据
     * @param boolean $writeFile 是否写入配置文件
     */
    public static function setPluginConfig($name, $config, $writeFile = true)
    {
        $plugin = self::getPluginInstance($name);
        $plugin->setConfig($name, $config);
        $fullConfig = self::getPluginFullConfig($name);
        foreach ($fullConfig as $k => &$v) {
            if (isset($config[$v['name']])) {
                $value = $v['type'] !== 'array' && is_array($config[$v['name']]) ? implode(',', $config[$v['name']]) : $config[$v['name']];
                $v['value'] = $value;
            }
        }
        if ($writeFile) {
            // 写入配置文件
            self::setPluginFullConfig($name, $fullConfig);
        }
        return true;
    }

    /**
     * 获取插件类的配置数组
     * @param string $name 插件名
     * @return array
     */
    public static function getPluginFullConfig($name)
    {
        $plugin = self::getPluginInstance($name);
        if (!$plugin) {
            return [];
        }
        return $plugin->getFullConfig($name);
    }

    /**
     * 写入配置文件
     * @param string $name 插件名
     * @param array $array
     * @return boolean
     * @throws \Exception
     */
    public static function setPluginFullConfig($name, $array)
    {
        $file = self::pluginPath($name) . DIRECTORY_SEPARATOR . 'config.php';
        if (!self::checkReallyWritable($file)) {
            throw new \Exception("文件没有写入权限");
        }
        if ($handle = fopen($file, 'w')) {
            fwrite($handle, "<?php\n\n" . "return " . var_export($array, TRUE) . ";\n");
            fclose($handle);
        } else {
            throw new \Exception("文件没有写入权限");
        }
        return true;
    }

    /**
     * 检测文件或文件夹是否可写
     * @param $file
     * @return bool
     */
    public static function checkReallyWritable($file)
    {
        if (DIRECTORY_SEPARATOR === '/') {
            return is_writable($file);
        }
        if (is_dir($file)) {
            $file = rtrim($file, '/') . '/' . md5(mt_rand());
            if (($fp = @fopen($file, 'ab')) === FALSE) {
                return FALSE;
            }
            fclose($fp);
            @chmod($file, 0777);
            @unlink($file);
            return TRUE;
        } elseif (!is_file($file) OR ($fp = @fopen($file, 'ab')) === FALSE) {
            return FALSE;
        }
        fclose($fp);
        return TRUE;
    }


    /**
     * 设置基础配置信息
     * @param string $name 插件名
     * @param array $array
     * @return boolean
     * @throws \Exception
     */
    public static function setPluginInfo($name, $array)
    {
        $file = self::pluginPath($name) . DIRECTORY_SEPARATOR . 'info.ini';
        $plugin = self::getPluginInfo($name);
        $array = $plugin->setInfo($name, $array);
        $res = array();
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                $res[] = "[$key]";
                foreach ($val as $skey => $sval)
                    $res[] = "$skey = " . (is_numeric($sval) ? $sval : $sval);
            } else
                $res[] = "$key = " . (is_numeric($val) ? $val : $val);
        }
        if ($handle = fopen($file, 'w')) {
            fwrite($handle, implode("\n", $res) . "\n");
            fclose($handle);
            //清空当前配置缓存
            self::delCache($name, 'plugins');
        } else {
            throw new \Exception("文件没有写入权限");
        }
        return true;
    }

    /**
     * 获取插件的单例
     * @param $name
     * @return mixed|null
     */
    public static function getPluginInstance($name)
    {
        static $_plugins = [];
        if (isset($_plugins[$name])) {
            return $_plugins[$name];
        }
        $class = self::getPluginClass($name);
        if (class_exists($class)) {
            $_plugins[$name] = new $class();
            return $_plugins[$name];
        } else {
            return null;
        }
    }

    /**
     * 初始化插件路由
     * @param $name
     */
    public static function initPluginsUrlRules()
    {
        $urlRules = self::getCache('', 'pluginsUrlRules', []);
        $plugins = self::getCache('', 'plugins', []);
        if (empty($urlRules) && $plugins) {
            foreach ($plugins as $key => $plugin) {
                $urlRules["{$key}/<a:\w+>"] = "plugins/{$key}/{$key}/<a>";
                $urlRules["{$key}/<controller:\w+>/<a:\w+>"] = "plugins/{$key}/<controller>/<a>";
            }
            self::setCache('', $urlRules, 'pluginsUrlRules');
        }
        \Yii::$app->urlManager->addRules($urlRules);
    }

    /**
     * 插件国际化语义初始化
     * @param string $name
     */
    public static function initPluginsMessages()
    {
        $plugins = self::getCache('', 'plugins', []);
        foreach ($plugins as $key => $plugin) {
            if (empty($plugin)) {
                continue;
            }
            if (!isset(\Yii::$app->i18n->translations[$key . '*'])) {
                $fileMap = Common::getCache($key . '.messages', 'plugins');
                if (!$fileMap) {
                    $files = FileHelper::findFiles($plugin['path'], ['only' => ['controllers/*.php']]);
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
                \Yii::$app->i18n->translations[$key . '*'] = [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => $plugin['path'] . DIRECTORY_SEPARATOR . 'messages',
                    'fileMap' => $fileMap,
                ];
            }
        }
//        var_dump(\Yii::$app->i18n->translations);die;
    }
}