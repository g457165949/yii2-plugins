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
            'config' => $config
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
        $post = \Yii::$app->request->post();
        if (empty($post['name'])) {
            return Common::error(Common::t('Parameter %s can not be empty', 'name'));
        }

        try{
            (new PluginManager())->install($post['name'], false, $post);
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
            (new PluginManager())->uninstall($name, $force);
        } catch (Exception $e) {
            return Common::error($e->getMessage());
        }
        return Common::success(Common::t('Uninstall successful'));
    }

    /**
     * 更新插件
     */
    public function upgrade()
    {
        $name = \Yii::$app->request->post("name");
        if (!$name) {
            return Common::error(Common::t('Parameter %s can not be empty', 'name'));
        }
        try {
            $uid = $this->request->post("uid");
            $token = $this->request->post("token");
            $version = $this->request->post("version");
            $faversion = $this->request->post("faversion");
            $extend = [
                'uid'       => $uid,
                'token'     => $token,
                'version'   => $version,
                'faversion' => $faversion
            ];
            //调用更新的方法
            (new PluginManager())->upgrade($name, $extend);
            Common::success(Common::t('Operate successful'));
        } catch (\Exception $e) {
            return Common::error($e->getMessage());
        }
    }

    /**
     * 启用
     */
    public function actionEnable()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $name = \Yii::$app->request->post("name");
        $extend = \Yii::$app->request->post("extend");
        if (!$name) {
            return Common::error(Common::t('Parameter %s can not be empty', 'name'));
        }
        try {
            (new PluginManager())->enable($name, $extend);
        } catch (Exception $e) {
            return Common::error($e->getMessage());
        }
        return Common::success(Common::t('Enable successful'));
    }

    /**
     * 停用
     */
    public function actionDisable()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $name = \Yii::$app->request->post("name");
        $extend = \Yii::$app->request->post("extend");
        if (!$name) {
            return Common::error(Common::t('Parameter %s can not be empty', 'name'));
        }
        try {
            (new PluginManager())->disable($name, $extend);
        } catch (Exception $e) {
            return Common::error($e->getMessage());
        }
        return Common::success(Common::t('Disable successful'));
    }
}