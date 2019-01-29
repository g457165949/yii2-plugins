<?php
namespace app\plugins\menu;

use zyh\plugins\components\Plugin;


/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/1/22
 * Time: 下午6:13
 */
class Menu extends Plugin
{

    public function hooks()
    {
        return [
            'admin_login_init' => 'loginInit',
        ];
    }

    public function events()
    {
        return [
            'app\controllers\SiteController' => [
                'admin_login_init' => [
                    'loginInit2',
                    ['loginInit3','adsfasdf',false]
                ]
            ],
        ];
    }

    public function loginInit($params, $extra)
    {
//        echo 111111;
    }

    public function loginInit2($event)
    {
//        echo 111111;
    }

    public function loginInit3($event)
    {
//        echo 33333;
    }


    public function install()
    {
        return true;
    }

    public function uninstall()
    {
        return true;
    }
}