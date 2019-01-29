Plugins
=======
这是一个基于Yii插件的管理系统,可使用Yii自带的事件及自定义钩子hooks来创建自定义的插件

Installation
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


Yii Config
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

Hook Usage
-----

```php

 Hook::listen('admin_login_init',$params);
 
 ```
 
Event Usage
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

例子
-----
待上传中.......