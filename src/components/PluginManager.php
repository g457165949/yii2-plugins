<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/1/24
 * Time: 上午10:33
 */

namespace zyh\plugins\components;


use yii\base\Exception;
use yii\helpers\FileHelper;
use zyh\plugins\services\Service;

class PluginManager
{

    public static function install($name, $force = false, $extend)
    {
        if (!$name || (is_dir(Common::pluginPath($name)) && !$force)) {
            throw new \Exception('Plugin already exists');
        }

        // 下载
        $tmpFile = Service::download($name, $extend);
        // 解压
        $plauginsDir = Service::unzip($name);
        // 移除临时文件
        @unlink($tmpFile);
        // 检查插件是否完整
        Service::check($name);

        // 复制文件
        $sourceAssetsDir = $plauginsDir . DIRECTORY_SEPARATOR . 'assets';
        $destAssetsDir = self::getDestAssetsDir($name);
        if (is_dir($sourceAssetsDir)) {
            FileHelper::copyDirectory($sourceAssetsDir, $destAssetsDir);
        }

        try {
            // 默认启用该插件
            $info = Common::getPluginInfo($name);
            if (!$info['state']) {
                $info['state'] = 1;
                Common::setPluginInfo($name, $info);
            }

            // 执行安装脚本
            $class = Common::getPluginClass($name);
            if (class_exists($class)) {
                $plugin = new $class();
                $plugin->install();
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        // 导入sql
        Service::importSql($name);

        return true;
    }

    public static function uninstall($name, $force)
    {
        if (!$name || !is_dir(Common::pluginPath($name))) {
            throw new Exception('Plugin not exists');
        }

        // 移除插件基础资源目录
//        $destAssetsDir = self::getDestAssetsDir($name);
//        if (is_dir($destAssetsDir)) {
//            rmdirs($destAssetsDir);
//        }

        // 执行卸载脚本
        try {
            $class = Common::getPluginClass($name);
            if (class_exists($class)) {
                $plugin = new $class();
                $plugin->uninstall();
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        // 移除插件目录
        FileHelper::removeDirectory(Common::pluginPath($name));
        return true;
    }

    /**
     * 启用插件
     * @param $name
     * @param array $extend
     * @return bool
     * @throws Exception
     */
    public static function enable($name, $extend = [])
    {
        if (!$name || !is_dir(Common::pluginPath($name))) {
            throw new Exception('Plugin not exists');
        }

        // 移除插件基础资源目录
//        $destAssetsDir = self::getDestAssetsDir($name);
//        if (is_dir($destAssetsDir)) {
//            rmdirs($destAssetsDir);
//        }

        // 执行脚本
        try {
            $class = Common::getPluginClass($name);
            if (class_exists($class)) {
                $plugin = new $class();
                $plugin->enable();
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        $info = Common::getPluginInfo($name);
        $info['state'] = 1;
        unset($info['url']);

        Common::setPluginInfo($name, $info);
        return true;
    }

    /**
     * 禁用插件
     * @param $name
     * @param array $extend
     * @return bool
     * @throws Exception
     */
    public static function disable($name, $extend = [])
    {
        if (!$name || !is_dir(Common::pluginPath($name))) {
            throw new Exception('Plugin not exists');
        }

        // 移除插件基础资源目录
//        $destAssetsDir = self::getDestAssetsDir($name);
//        if (is_dir($destAssetsDir)) {
//            rmdirs($destAssetsDir);
//        }

        // 删除配置
        $info = Common::getPluginInfo($name);
        $info['state'] = 0;
        unset($info['url']);

        Common::setPluginInfo($name, $info);

        // 执行脚本
        try {
            $class = Common::getPluginClass($name);
            if (class_exists($class)) {
                $plugin = new $class();
                $plugin->disable();
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        return true;
    }

    /**
     * 获取插件源资源文件夹
     * @param   string $name 插件名称
     * @return  string
     */
    protected static function getSourceAssetsDir($name)
    {
        return Common::pluginPath($name) . DIRECTORY_SEPARATOR . 'assets';
    }

    /**
     * 获取插件目标资源文件夹
     * @param   string $name 插件名称
     * @return  string
     */
    protected static function getDestAssetsDir($name)
    {
        return \Yii::getAlias('@webroot');
//        $assetsDir = \Yii::getAlias('@app/web') . "/static/{$name}/";
//        if (!is_dir($assetsDir)) {
//            FileHelper::createDirectory($assetsDir);
//        }
//        return $assetsDir;
    }
}