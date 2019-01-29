<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/1/22
 * Time: 下午6:16
 */

namespace zyh\plugins\components;


use yii\base\BaseObject;

abstract class Plugin extends BaseObject
{
    public $pluginPath;

    public function init()
    {
        parent::init();
        $class = get_class($this);
        $reflection = new \ReflectionClass($class);
        $this->pluginPath = dirname($reflection->getFileName());
    }

    /**
     * 全局钩子 例如:
     *      [hookName => methodName]
     *      [hookName => [methodName,_overlay => true]]  覆盖事件
     * @return array
     */
    public function hooks()
    {
        return [];
    }

    /*
     * 事件 例如
    * an array with structure: [
    *      $eventSenderClassName => [
    *          $eventName1 => [
    *              $handlerMethodName
    *          ],
    *          $eventName2 => [
    *              [$handlerMethodName,$data,false]
    *          ],
    *      ]
    * ]
    *
    */
    public function events()
    {
        return [];
    }

    /**
     * 安装
     * @return mixed
     */
    abstract public function install();

    /**
     * 卸载
     * @return mixed
     */
    abstract public function uninstall();

    /**
     * 启用
     * @return bool
     */
    protected function enable()
    {
        return true;
    }

    /**
     * 禁用
     * @return bool
     */
    protected function disable()
    {
        return true;
    }

    /**
     * 更新
     * @return bool
     */
    protected function upgrade()
    {
        return true;
    }

    /**
     * 获取插件的配置数组
     * @param string $name 可选模块名
     * @return array
     */
    final public function getConfig($name = '')
    {
        if (empty($name)) {
            $name = $this->getName();
        }
        $config = Common::getCache($name, 'plugins');
        if ($config) {
            return $config;
        }
        $configFile = $this->pluginPath . DIRECTORY_SEPARATOR . 'config.php';
        if (is_file($configFile)) {
            $temp_arr = include $configFile;
            foreach ($temp_arr as $key => $value) {
                $config[$value['name']] = $value['value'];
            }
            unset($temp_arr);
        }
        Common::setCache($name, $config, 'plugins');

        return $config;
    }

    /**
     * 读取基础配置信息
     * @param string $name
     * @return array
     */
    final public function getInfo($name = '')
    {
        if (empty($name)) {
            $name = $this->getName();
        }

        $info = Common::getCache($name, 'plugins');
        if ($info) {
            return $info;
        }

        $infoFile = $this->pluginPath . DIRECTORY_SEPARATOR . 'info.ini';
        if (is_file($infoFile)) {
            $info = Common::parse($infoFile);
            $info['path'] = $this->pluginPath;
        }
        Common::setCache($name, $info, 'plugins');
        return $info ? $info : [];
    }

    /**
     * 设置插件信息数据
     * @param $name
     * @param array $value
     * @return array
     */
    final public function setInfo($name = '', $value = [])
    {
        if (empty($name)) {
            $name = $this->getName();
        }
        $info = $this->getInfo($name);
        $info = array_merge($info, $value);
        Common::setCache($name, $info, 'plugins');
        return $info;
    }

    /**
     * 检查基础配置信息是否完整
     * @return bool
     */
    final public function checkInfo()
    {
        $info = $this->getInfo();
        $info_check_keys = ['name', 'title', 'intro', 'author', 'version', 'state'];
        foreach ($info_check_keys as $value) {
            if (!array_key_exists($value, $info)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 获取当前模块名
     * @return string
     */
    final public function getName()
    {
        $data = explode('\\', get_class($this));
        return strtolower(array_pop($data));
    }
}