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
use zyh\plugins\components\Http;
use zyh\plugins\components\PluginManager;
use zyh\plugins\models\Plugins;
use zyh\plugins\models\searchs\PluginsSearch;

class PluginsController extends Controller
{
    /**
     * 插件市场所有插件信息
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new PluginsSearch();
        $dataProvider = $searchModel->search(\Yii::$app->requestedParams);

        $config['plugins'] = Common::getCache(null, 'plugins', []);

        foreach ($config['plugins'] as $k => &$v) {
            $v['config'] = Common::getPluginConfig($v['name']) ? 1 : 0;
        }

        \Yii::$app->view->params['config'] = $config;
        return $this->render('/plugins/index', [
            'config' => $config,
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * 插件详情
     * @return string
     */
    public function actionView()
    {
        $model = Plugins::findOne(\Yii::$app->request->get('id'));
        return $this->render('/plugins/view', ['model' => $model]);
    }

    /**
     * 本地的所有插件
     * @return string
     */
    public function actionDownloaded()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $offset = (int)\Yii::$app->request->get("offset");
        $limit = (int)\Yii::$app->request->get("limit");
        $filter = \Yii::$app->request->get("filter");
        $search = \Yii::$app->request->get("search");
        $search = htmlspecialchars(strip_tags($search));

        $onlinePlugins = Common::getCache(null, "onlinePlugins");
        if (!is_array($onlinePlugins)) {
            $onlinePlugins = [];
            $result = Http::sendRequest(Common::$_pluginConfig['params']['downloadUrl']);
            if ($result['ret']) {
                $json = json_decode($result['msg'], TRUE);
                $rows = isset($json['rows']) ? $json['rows'] : [];
                foreach ($rows as $index => $row) {
                    $onlinePlugins[$row['name']] = $row;
                }
            }
            Common::setCache(null, "onlinePlugins", $onlinePlugins, 600);
        }
        $filter = (array)json_decode($filter, true);
        $plugins = Common::getCache(null, 'plugins', []);
        $list = [];
        foreach ($plugins as $k => $v) {
            if ($search && stripos($v['name'], $search) === FALSE && stripos($v['intro'], $search) === FALSE)
                continue;

            if (isset($onlinePlugins[$v['name']])) {
                $v = array_merge($v, $onlinePlugins[$v['name']]);
            } else {
                $v['category_id'] = 0;
                $v['flag'] = '';
                $v['banner'] = '';
                $v['image'] = '';
                $v['donateimage'] = '';
                $v['demourl'] = '';
                $v['price'] = '0.00';
                $v['screenshots'] = [];
                $v['releaselist'] = [];
            }
            $v['createtime'] = filemtime(Common::pluginPath($v['name']));
            if ($filter && isset($filter['category_id']) && is_numeric($filter['category_id']) && $filter['category_id'] != $v['category_id']) {
                continue;
            }
            $list[] = $v;
        }
        $total = count($list);
        if ($limit) {
            $list = array_slice($list, $offset, $limit);
        }
        $result = array("total" => $total, "rows" => $list);

        if (\Yii::$app->request->get('callback')) {
            \Yii::$app->response->format = Response::FORMAT_JSONP;
            return [
                'data' => $result,
                'callback' => \Yii::$app->request->get('callback')
            ];
        }
        return $result;
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

        try {
            (new PluginManager())->install($post['name'], false, $post);
            $info = Common::getPluginInfo($post['name']);
            $info['config'] = Common::getPluginConfig($post['name']) ? 1 : 0;
            $info['state'] = 1;
        } catch (Exception $e) {
            return Common::error($e->getMessage());
        }
        return Common::success();
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
    public function actionUpgrade()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $name = \Yii::$app->request->post("name");
        if (!$name) {
            return Common::error(Common::t('Parameter %s can not be empty', 'name'));
        }
        try {
            $uid = \Yii::$app->request->post("uid");
            $token = \Yii::$app->request->post("token");
            $version = \Yii::$app->request->post("version");
            $faversion = \Yii::$app->request->post("faversion");
            $extend = [
                'uid' => $uid,
                'token' => $token,
                'version' => $version,
                'faversion' => $faversion
            ];
            //调用更新的方法
            (new PluginManager())->upgrade($name, $extend);
            return Common::success(Common::t('Operate successful'));
        } catch (\Exception $e) {
            return Common::error($e->getMessage());
        }
    }


    /**
     * 配置
     */
    public function actionConfig($ids = NULL)
    {
        $this->layout = 'main-base';
        $name = \Yii::$app->request->get("name");
        if (!$name) {
            return Common::error(Common::t('Parameter %s can not be empty', $ids ? 'id' : 'name'));
        }
        if (!is_dir(Common::pluginPath($name))) {
            return Common::error(Common::t(('Directory not found')));
        }
        $info = Common::getPluginInfo($name);
        $config = Common::getPluginFullConfig($name);
        if (!$info)
            Common::error(Common::t(('No Results were found')));
        if (\Yii::$app->request->isPost) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $params = \Yii::$app->request->post("row");
            if ($params) {
                foreach ($config as $k => &$v) {
                    if (isset($params[$v['name']])) {
                        if ($v['type'] == 'array') {
                            $params[$v['name']] = is_array($params[$v['name']]) ? $params[$v['name']] : (array)json_decode($params[$v['name']], true);
                            $value = $params[$v['name']];
                        } else {
                            $value = is_array($params[$v['name']]) ? implode(',', $params[$v['name']]) : $params[$v['name']];
                        }
                        $v['value'] = $value;
                    }
                }
                try {
                    //更新配置文件
                    Common::setPluginFullConfig($name, $config);
//                    Service::refresh();
                    return Common::success();
                } catch (\Exception $e) {
                    return Common::error(Common::t(($e->getMessage())));
                }
            }
            return Common::error(Common::t('Parameter %s can not be empty', ''));
        }
        $tips = [];
        foreach ($config as $index => &$item) {
            if ($item['name'] == '__tips__') {
                $tips = $item;
                unset($config[$index]);
            }
        }
        return $this->render('config', [
            'plugins' => ['info' => $info, 'config' => $config, 'tips' => $tips]
        ]);
    }


    /**
     * 启用/禁用
     */
    public function actionState()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $name = \Yii::$app->request->post("name");
        $extend = \Yii::$app->request->post("extend");
        if (!$name) {
            return Common::error(Common::t('Parameter %s can not be empty', 'name'));
        }
        try {
            if (\Yii::$app->request->post("action") == 'enable') {
                (new PluginManager())->enable($name, $extend);
            } else {
                (new PluginManager())->disable($name, $extend);
            }

        } catch (Exception $e) {
            return Common::error($e->getMessage());
        }
        return Common::success(Common::t('Operate successful'));
    }
}