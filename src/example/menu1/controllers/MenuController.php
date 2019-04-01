<?php
namespace app\plugins\menu\controllers;
use app\components\Fun;
use zyh\plugins\components\Common;
use zyh\plugins\components\PluginBaseController;

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/1/21
 * Time: 下午2:02
 */
class MenuController extends PluginBaseController
{

    public function actionIndex(){
//        echo Fun::T('test1');die;
        return $this->render('menu/index');
    }
}