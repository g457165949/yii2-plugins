<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/1/22
 * Time: 下午5:29
 */

namespace app\plugins\menu\controllers;

use app\components\Fun;
use zyh\plugins\components\PluginBaseController;

class TestController extends PluginBaseController
{
    public function actionIndex()
    {
        return $this->render('test/index');
    }
}