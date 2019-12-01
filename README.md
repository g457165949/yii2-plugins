Plugins
=======
这是一个基于Yii插件的管理系统,可使用Yii自带的事件及自定义钩子hooks来创建自定义的插件

## **主要特性**

* 基于Yii模块创建的插件管理系统
    * 支持Yii的局部、全局、Event事件的使用
    * 支持Hook的使用
    * 支持自定义国际化语义
    * 支持自定义模板页面
    * 支持自定义布局
    * 支持自定义安装sql文件
    * 支持安装、卸载、更新、禁用、启用时自定义的方法

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

Once the extension is installed, simply use it in your config/web.php code by :

```php
    // 插件自定义配置(默认不用配置)，如需自定义请修改其中项
    'params' => [
        'zyh.plugins.configs' => [
            'db' => 'db' // 配置中的db组件名
            'cache' => 'cache' //配置中的cache组件名
            'pluginsTable' => 'plugins' // 插件表名
            'pluginNamespace' => 'app\plugins' // 插件所在的命名空间
            'pluginRoot => '@app/plugins'' // 插件所在的物理路径，支持别名
            'pluginScope' => [] // 项目范围 (默认所有项目，如需个别项目使用，请填写项目配置文件中的id）
            ......   // 其他配置，请见 zyh\plugins\components\Configs.php
        ]
    ]
    ......
    //模块配置
    'modules'=>[
        'plugins' => [
            'class' => 'zyh\plugins\Module',
            /*
            //自定义插件管理后台控制controller
            'controllerMap' => [
                'plugins' => [
                    // 指向自己的控制器
                    'class' => 'app\controllers\PluginsController',
                ]
            ],
             // 可自定义自己页面位置
            'layout' => 'main', // 布局名称
            'layoutPath' => '@app/views/layouts', // 布局位置
            'viewPath' => '@app/views', // 页面位置
            */
        ],
        ...
    ]
```

导入数据库
-----
```shell
./yii migrate --migrationPath=@zyh/plugins/migrations
```

使用方式
-----
先对Yii模块进行配置,再拷贝事例中的代码,直接运行


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
\Yii::trigger('admin_login_init')
 
or 

// Yii事件
Event::trigger('admin_login_init');

```

## 插件目录说明
拷贝example下menu到你创建的pluginRoot目录文件夹下

- [assets](#assets) (js,css,img)资源拷贝项目web目录下
- [controllers](#controllers) 插件控制(必须有)
- [messages](#messages) 自定义语义
- views 插件模板和Yii模板文件使用方式一致(可自定义主题)
- [info.ini](#info) 插件配置(必需有)
- install.sql 插件安装后导入的sql语句
- [Menu.php](#插件类) 插件主类(必需有)
- [MenuAsset.php](#资源类) 如有js,img,css存放地址


## assets 
```sh
js/
img
css
```

## controllers
```php
<?php
class MenuController extends PluginBaseController
{

    public function actionIndex(){
        // 插件国际化语义调用方式 输出:测试
        echo \Yii::t('menu','test');
        return $this->render('menu/index');
    }
}
```
**注意**: 如果插件名和控制器名一致路由为(menu/action)和(menu/controller/action)

## messages
```php
<?php
return [
   "Test" => "测试", 
];

````
**注意**: 如果插件名和控制器名一致翻译文件命名为插件名(例如:menu.php)和插件名/controller(例如:menu/test)

## info
```php
name = menu
title = 菜单插件
intro = 可在线执行FastAdmin的命令行相关命令
author = ZhangYongHui
website = http://www.fastadmin.net
version = 1.0.5
state = 1
```


## 插件类
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

    /**
     * $params 参数
     * $extra  其他参数
     */
    public function loginInit($params, $extra)
    {
        echo "登录初始化";
    }

    /**
     * $event 事件
     */
    public function loginInit2($event)
    {
        echo "登录初始化";
    }

    public function loginInit3($event)
    {
        echo "登录初始化";
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

## 资源类
```php
use yii\web\AssetBundle;

class MenuAsset extends AssetBundle
{
    //资源文件的源文件位置
    public $sourcePath = '@app/plugins/menu/assets';
}
```

```php
// 页面资源调用方式
$configJs = MenuAsset::register($this)->baseUrl.'/js/config.js';
```
