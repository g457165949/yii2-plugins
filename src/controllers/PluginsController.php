<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/1/20
 * Time: 下午5:40
 */

namespace zyh\plugins\controllers;


use yii\base\Exception;
use yii\web\Controller;
use yii\web\Response;
use zyh\plugins\components\Common;
use zyh\plugins\components\PluginManager;

class PluginsController extends Controller
{

    /**
     * 插件市场所有插件信息
     * @return string
     */
    public function actionIndex()
    {
        $config = \Yii::$app->view->params['config'];
        $config['plugins'] = Common::getCache(null, 'plugins');

        \Yii::$app->view->params['config'] = $config;
        return $this->render('/plugins/index', [
            'config' => \Yii::$app->params
        ]);
    }

    /**
     * 本地的所有插件
     * @return string
     */
    public function actionDownloaded()
    {
        $config = \Yii::$app->view->params['config'];
        $config['plugins'] = Common::getCache(null, null, 'plugins');

        \Yii::$app->view->params['config'] = $config;
        return $this->render('/plugins/index', [
            'config' => \Yii::$app->params
        ]);
    }

    /**
     * 安装
     */
    public function actionInstall()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
//        $post = \Yii::$app->request->post();
        $post = \Yii::$app->request->get();
        if (empty($post['name'])) {
            return Common::error(Common::t('Parameter %s can not be empty', 'name'));
        }

        try{
            PluginManager::install($post['name'], false, $post);
            $info = Common::getPluginInfo($post['name']);
            $info['config'] = Common::getPluginConfig($post['name']) ? 1 : 0;
            $info['state'] = 1;
        }catch (Exception $e){
            return Common::error($e->getMessage());
        }
        return Common::success($info);
    }

    /**
     * 卸载
     */
    public function actionUninstall()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $name = \Yii::$app->request->post("name");
        $force = \Yii::$app->request->post("force");
        if (!$name) {
            return Common::error(Common::t('Parameter %s can not be empty', 'name'));
        }
        try {
            PluginManager::uninstall($name, $force);
        } catch (Exception $e) {
            return Common::error($e->getMessage());
        }
        return Common::success(Common::t('Uninstall successful'));
    }

    /**
     * 启用
     */
    public function actionEnable()
    {
        // 修改info.ini文件信息

        // 修改插件缓存中的信息
    }

    /**
     * 停用
     */
    public function actionDisable()
    {
        // 修改info.ini文件信息

        // 修改插件缓存中的信息
    }
}