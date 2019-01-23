<?php

namespace zacksleo\yii2\plugin\controllers;

use yii;
use yii\helpers\Url;
use yii\web\Controller;
use yii\filters\AccessControl;
use zacksleo\yii2\plugin\components\PluginManger;

/**
 * Class PluginManageController
 * @package zacksleo\yii2\plugin\controllers
 * @property \zacksleo\yii2\plugin\components\PluginManger $pluginManger
 */
class PluginManageController extends Controller
{
    public $layout;
    public $adminLayout;
    public $menu = [];
    public $defaultIcon;
    public $module;
    private $folder;
    private $plugins = [];
    private $pluginManger;
    public $enableCsrfValidation = false;

    /**
     * @inheritdoc
     */
//    public function behaviors()
//    {
//        return [
//            'access' => [
//                'class' => AccessControl::className(),
//                'only' => ['index', 'upgrade'],
//                'rules' => [
//                    [
//                        'allow' => true,
//                        'roles' => ['@'],
//                    ],
//                ],
//            ],
//
//        ];
//    }

    public function init()
    {
        parent::init();
        $this->module = Yii::$app->getModule('plugin');
        $this->folder = Yii::getAlias($this->module->pluginRoot);
        $this->pluginManger = new PluginManger();
        $this->adminLayout = $this->module->layout;
        $publishedPath = Yii::$app->getAssetManager()->publish($this->module->moduleDir . DIRECTORY_SEPARATOR . 'default.png');
        $this->defaultIcon = Url::to($publishedPath[1], true);
    }

    public function actionIndex()
    {
        $this->_getPlugins($this->folder);
        $this->_loadPlugins();
        $this->_setMenu();
        $plugins = [
            PluginManger::STATUS_ENABLED => [],
            PluginManger::STATUS_INSTALLED => [],
            PluginManger::STATUS_NOT_INSTALLED => []
        ];
        foreach ($this->plugins as $plugin) {
            switch ($this->pluginManger->status($plugin['plugin'])) {
                case PluginManger::STATUS_ENABLED:
                    $plugins[PluginManger::STATUS_ENABLED][] = $plugin;
                    break;
                case PluginManger::STATUS_INSTALLED:
                    $plugins[PluginManger::STATUS_INSTALLED][] = $plugin;
                    break;
                case PluginManger::STATUS_NOT_INSTALLED:
                    $plugins[PluginManger::STATUS_NOT_INSTALLED][] = $plugin;
                    break;
            }
        }
        return $this->render('index', ['plugins' => $plugins]);
    }

    public function actionSetting($id)
    {
        $this->_setMenu();
        $plugin = $this->_loadPluginFromIdentify($id);
        if (method_exists($plugin, 'AdminCp')) {
            $content =$plugin->admincp();
        } elseif ($plugin->setAdminCp()) {
            $file = $plugin->setAdminCp();
            include_once($plugin->pluginDir . DIRECTORY_SEPARATOR . $file . '.php');
            if (strpos($file, '/') !== false) {
                $class = end(explode('/', $file));
            } else {
                $class = $file;
            }
            if (!class_exists($class)) {
                $this->redirect(array('plugin/PluginManage/index'));
                exit;
            }
            $AdminCp = new $class();
            $AdminCp->Owner($plugin);
            $content = $AdminCp->run();
        } else {
            return $this->redirect(array('plugin/PluginManage/index'));
        }
        return $this->render('setting', ['name' => $plugin->name, 'content' => $content]);
    }

    public function actionMarket()
    {
    }

    public function actionInstall()
    {
        if (!isset($_POST['id'])) {
            $this->_ajax(0);
        }
        $id = $_POST['id'];
        $plugin = $this->_loadPluginFromIdentify($id);
        $result = $this->pluginManger->install($plugin);
        if ($result) {
            $this->_ajax(1);
        } else {
            $this->_ajax(0);
        }
    }

    public function actionUninstall()
    {
        if (!isset($_POST['id'])) {
            $this->_ajax(0);
        }
        $id = $_POST['id'];
        $plugin = $this->_loadPluginFromIdentify($id);
        $result = $this->pluginManger->uninstall($plugin);
        if ($result) {
            $this->_ajax(1);
        } else {
            $this->_ajax(0);
        }
    }

    public function actionEnable()
    {
        if (!isset($_POST['id'])) {
            $this->_ajax(0);
        }
        $id = $_POST['id'];
        $plugin = $this->_loadPluginFromIdentify($id);
        if ($this->pluginManger->enable($plugin)) {
            $this->_setMenu(true);
            $this->_ajax(1);
        } else {
            $this->_ajax(0);
        }
    }

    public function actionDisable()
    {
        if (!isset($_POST['id'])) {
            $this->_ajax(0);
        }
        $id = $_POST['id'];
        $plugin = $this->_loadPluginFromIdentify($id);
        if ($this->pluginManger->disable($plugin)) {
            $this->_setMenu(true);
            $this->_ajax(1);
        } else {
            $this->_ajax(0);
        }
    }

    private function _getPlugins($folder)
    {
        if ($handle = opendir($folder)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    if (is_dir($folder . DIRECTORY_SEPARATOR . $file)) {
                        $this->_getPlugins($folder . DIRECTORY_SEPARATOR . $file);
                    } else {
                        if ($file == 'composer.json') {
                            $contents = json_decode(file_get_contents($folder . '/composer.json'), true);
                            $class = $contents['extra']['class'];
                            if (class_exists($class)) {
                                $this->plugins[] = ['path' => $folder, 'pluginEntry' => $file];
                            }
                        }
                    }
                }
            }
            closedir($handle);
        } else {
            return false;
        }
    }

    private function _loadPlugins()
    {
        $plugins = [];
        foreach ($this->plugins as $k => $plugin) {
            $contents = json_decode(file_get_contents($plugin['path'] . '/composer.json'), true);
            $class = $contents['extra']['class'];
            if (class_exists($class)) {
                $this->plugins[$k]['plugin'] = new $class();
                $this->plugins[$k]['status'] = $this->pluginManger->status($this->plugins[$k]['plugin']);
                $this->plugins[$k]['identify'] = $this->plugins[$k]['plugin']->identify;
                $plugins[$k] = $this->plugins[$k];
                unset($plugins[$k]['plugin']);
            }
        }
        Yii::$app->cache->set('PluginList', $plugins);
        return true;
    }

    private function _loadPluginFromIdentify($identify)
    {
        $plugins = Yii::$app->cache->get('PluginList');
        if (!$plugins) {
            $this->_getPlugins($this->folder);
            $this->_loadPlugins();
            $plugins = $this->plugins;
        }
        foreach ($plugins as $plugin) {
            if ($plugin['identify'] == $identify) {
                $contents = json_decode(file_get_contents($plugin['path'] . '/composer.json'), true);
                $class = $contents['extra']['class'];
                if (class_exists($class)) {
                    return new $class();
                }
            }
        }
        return false;
    }

    private function _setMenu($force = false)
    {
        if (!$force) {
            $cache = Yii::$app->cache->get('PluginMenu');
        }
        if (isset($cache)) {
            $this->menu = $cache;
            return;
        }
        $this->menu = array();
        if (empty($this->plugins)) {
            $this->_getPlugins($this->folder);
            $this->_loadPlugins();
        }

        foreach ($this->plugins as $plugin) {
            if ($this->pluginManger->status($plugin['plugin']) != PluginManger::STATUS_ENABLED) {
                continue;
            }
            if (!method_exists($plugin, 'AdminCp') && !$plugin['plugin']->setAdminCp()) {
                continue;
            }
            $this->menu[] = array('label' => $plugin['plugin']->name, 'url' => array('/plugin/PluginManage/setting', 'id' => $plugin['plugin']->identify));
        }
        Yii::$app->cache->set('PluginMenu', $this->menu);
    }

    private function _ajax($status, $data = null)
    {
        header('Content-type: application/json');
        echo json_encode(array('status' => $status, 'data' => $data));
        Yii::$app->end();
    }
}
