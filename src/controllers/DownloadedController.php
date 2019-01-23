<?php

namespace zyh\plugins\controllers;

use app\components\BaseController;
use app\components\Fun;
use yii\helpers\FileHelper;
use zyh\addons\components\Common;
use zyh\addons\models\Addons;

class DownloadedController extends \yii\web\Controller
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
}