<?php

namespace app\plugins\menu\controllers;

use zyh\plugins\components\PluginBaseController;

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/1/21
 * Time: 下午2:02
 */
class MenuController extends PluginBaseController
{

//    public function actions()
//    {
//        return StaticFun::kitActions(['stop']);
//    }

    public function actionStop(){
        echo "show menu kit new stop2 run 。。。。。";die;
    }

    public function actionIndex()
    {
        return $this->render('menu/index');
    }
}