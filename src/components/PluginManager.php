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
use zacksleo\yii2\plugin\components\PluginManger;
use zyh\plugins\services\Service;

class PluginManager extends PluginManagerBase
{

    public function install($name, $force = false, $extend)
    {
        if (!$name || (is_dir(Common::pluginPath($name)) && !$force)) {
            throw new \Exception('Plugin already exists');
        }

        // 下载
        $tmpFile = $this->download($name, $extend);
        // 解压
        $plauginsDir = $this->unzip($name);
        // 移除临时文件
        @unlink($tmpFile);
        // 检查插件是否完整
        $this->check($name);

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
        $this->importSql($name);

        return true;
    }

    /**
     * 卸载
     * @param string $name 插件名
     * @param bool $force 是否强制执行
     * @return bool
     * @throws Exception
     */
    public function uninstall($name, $force = false)
    {
        if (!$name || !is_dir(Common::pluginPath($name))) {
            throw new Exception('Plugin not exists');
        }

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
     * 升级插件
     *
     * @param   string $name 插件名称
     * @param   array $extend 扩展参数
     */
    public function upgrade($name, $extend = [])
    {
        $info = Common::getPluginInfo($name);
        if ($info['state']) {
            throw new \Exception('Please disable plugin first');
        }

        $config = Common::getPluginConfig($name);
        if ($config) {
            //备份配置
        }

        // 备份插件文件
        $this->backup($name);

        // 远程下载插件
        $tmpFile = $this->download($name, $extend);

        // 解压插件
        $addonDir = $this->unzip($name);

        // 移除临时文件
        @unlink($tmpFile);

        if ($config) {
            // 还原配置
            Common::setPluginConfig($name, $config);
        }

        // 导入
        $this->importSql($name);

        // 执行升级脚本
        try {
            $class = Common::getPluginClass($name);
            if (class_exists($class)) {
                $plugin = new $class();

                if (method_exists($class, "upgrade")) {
                    $plugin->upgrade();
                }
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
        return true;
    }

    /**
     * 启用插件
     * @param $name
     * @param array $extend
     * @return bool
     * @throws Exception
     */
    public function enable($name, $extend = [])
    {
        if (!$name || !is_dir(Common::pluginPath($name))) {
            throw new Exception('Plugin not exists');
        }

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
    public function disable($name, $extend = [])
    {
        if (!$name || !is_dir(Common::pluginPath($name))) {
            throw new Exception('Plugin not exists');
        }

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
}