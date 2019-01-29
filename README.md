Plugins
=======
这是一个基于Yii插件的管理系统,可使用Yii自带的事件及自定义钩子hooks来创建自定义的插件

## **主要特性**

* 基于Yii模块创建的插件管理系统
    * 支持Yii的事件的使用
    * 支持Hook的使用
    * 支持插件自定义国际化语义
    * 支持插件自定义模板页面
    * 支持插件自定义布局
    * 支持插件自定义安装sql

    

安装
------------ 

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist zyh/yii2-plugins "master-dev"
```

or add

```
"zyh/yii2-plugins": "master-dev"
```

to the require section of your `composer.json` file.


Yii模块配置
-----

Once the extension is installed, simply use it in your code by :

```php
    //模块配置
    'modules'=>[
        'plugins' => [
            'class' => 'zyh\plugins\Module',
            'layout' => 'main',
            'layoutPath' => '@app/views/layouts', #布局
            'pluginRoot' => '@app/plugins', ##放置插件的namespace目录
            'pluginNamespace' => 'app\plugins',  ##放置插件的namespace
            'pluginDownloadUrl' => 'http://30071.dev.91gaoding.com'
        ],
        ...
    ]
```

Hook使用
-----

```php

 Hook::listen('admin_login_init',$params);

 ```
 
Yii Event使用
-----

```php
// 当前继承的component类
$this->trigger('admin_login_init');
 
or
 
// Yii全局事件
\Yii::on('admin_login_init')
 
or 

// Yii事件
Event::on('admin_login_init');

```

## 例子
拷贝example下menu到你创建的pluginRoot目录文件夹下

- [assets](#assets) (js,css,img)资源拷贝项目web目录下
- [controllers](#controllers) 插件控制
- [messages](#messages) 自定义语义
- views 插件模板和Yii模板文件加载方式一致
- [info.ini](#info.ini) 插件配置
- install.sql 插件安装后导入的sql语句
- [Menu.php](#插件.php) 插件主类


#assets 
```sh
js/
img
css
```

#controllers
```php
<?php
class MenuController extends PluginBaseController
{

    public function actionIndex(){
        echo Fun::T('test1');die;
        return $this->render('menu/index');
    }
}
```
**注意**: 如果插件名和控制器名一致路由为menu/action名,否则menu/controller名/action名

#messages
```php
<?php
return [
   "Test" => "测试", 
];

````
**注意**: 如果插件名和控制器名一致翻译文件名为插件名,(插件名/controller名)controller名

#info.ini
```php
name = menu
title = 菜单插件
intro = 可在线执行FastAdmin的命令行相关命令
author = ZhangYongHui
website = http://www.fastadmin.net
version = 1.0.5
state = 1
```
#插件.php
```php
use zyh\plugins\components\Plugin;

<?php

class Menu extends Plugin
{

    public function hooks()
    {
        return [
            'admin_login_init' => 'loginInit',  // 钩子名 => 方法名
        ];
    }

    public function events()
    {
        return [
            // 继承component的类名
            'app\controllers\SiteController' => [
                // 事件名
                'admin_login_init' => [
                    'loginInit2',   // 方法名
                    ['loginInit3','adsfasdf',false]  // [方法名,参数,追加]
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
        // 自定义新增方法,如(菜单)方法
        return true;
    }

    public function uninstall()
    {
        // 自定义删除方法,如(菜单)方法
        return true;
    }
}
```