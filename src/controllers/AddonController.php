<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/1/20
 * Time: 下午5:40
 */

namespace zyh\plugins\controllers;


use yii\web\Controller;

class AddonController extends Controller
{

    public function actionIndex()
    {

        $config = \Yii::$app->view->params['config'];
        $config['addons'] = Fun::getCache(null, null, 'addons');

        \Yii::$app->view->params['config'] = $config;
        return $this->render('/addon/index',[
            'config' => \Yii::$app->params
        ]);
    }

    public function actionDownloaded(){

    }
}