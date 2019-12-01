<?php
namespace app\plugins\menu;

use app\components\KitPlugin;
use yii\base\Event;
use zyh\plugins\components\Plugin;


/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/1/22
 * Time: 下午6:13
 */
class Menu extends KitPlugin
{

    // 钩子
    public function hooks()
    {
        return [
            'menu_init' => 'setMenu'
        ];
    }

    // 事件
    public function events()
    {
        return [
            'app\controllers\SiteController' => [
                'about_begin' => [
                    'loginInit2',
                ],
                'about_end' => [
                    ['loginInit3','adsfasdf',false]
                ]
            ],
        ];
    }

    /**
     * 添加菜单
     * @param $params
     */
    public function setMenu(&$params){
        $params[] = ['label' => 'Menu', 'url' => ['/menu/index']];
    }

    public function loginInit2($event)
    {
        var_dump($event->sender->uid);
    }

    public function loginInit3($event)
    {
        var_dump($event->data);
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