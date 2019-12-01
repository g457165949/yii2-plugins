<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\web\View;
use zyh\plugins\components\Common;
use zyh\plugins\models\Plugins;

/* @var $this yii\web\View */
/* @var $searchModel zyh\plugins\models\searchs\PluginsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Plugins';
$this->params['breadcrumbs'][] = $this->title;
?>
    <div class="plugins-index">

        <h1><?= Html::encode($this->title) ?></h1>

        <p>
            <?= Html::a('Create Plugins', ['create'], ['class' => 'btn btn-success']) ?>
        </p>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                'title',
                'name',
                'author',
                'intro:ntext',
                [
                    'label' => '当前版本',
                    'value' => function($model) use ($config){
                        return ArrayHelper::getValue($config['plugins'],$model->name)['version']?:"";
                    }
                ],
                [
                    'label' => '最新版本',
                    'value' => function($model){
                        return $model->version;
                    }
                ],
                [
                    'label' => '分类',
                    'value' => function($model) {
                        return Plugins::dropDownList('category_id',$model->category_id);
                    }
                ],
                [
                    'label' => '状态',
                    'value' => function($model) use ($config){
                        $info = ArrayHelper::getValue($config, "plugins.{$model->name}");
                        if($info){
                            if($info['state'] == 1){
                                return "已启用";
                            }else{
                                return "已禁用";
                            }
                        }
                        return "未安装";
                    }
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'header' => '操作',
                    'template' => '{view} {install} {uninstall} {upgrade} {state}',
                    'buttons' => [
                        'view' => function ($url, $model, $key) {
                            return Html::a('详情', ['/plugins/view', "id" => $model->id]);
                        },
                        'install' => function ($url, $model, $key) use ($config) {
                            if (!isset($config['plugins'][$model->name])) {
                                return Html::a(Common::T('Install'), "#", [
                                    'class' => 'plugins-button',
                                    'data-type' => 'install',
                                    'data-name' => $model->name
                                ]);
                            }
                        },
                        'uninstall' => function ($url, $model, $key) use ($config) {
                            $info = ArrayHelper::getValue($config, "plugins.{$model->name}");
                            if ($info && empty($info['state'])) {
                                return Html::a(Common::T('Uninstall'), "#", [
                                    'class' => 'plugins-button',
                                    'data-type' => 'uninstall',
                                    'data-name' => $model->name
                                ]);
                            }
                        },
                        'upgrade' => function ($url, $model, $key) use ($config) {
                            $info = ArrayHelper::getValue($config, "plugins.{$model->name}");
                            if ($info && empty($info['state']) && $info['version'] != $model->version) {
                                return Html::a(Common::T('Upgrade'), "#", [
                                    'class' => 'plugins-button',
                                    'data-type' => 'upgrade',
                                    'data-name' => $model->name,
                                    'data-version' => $model->version
                                ]);
                            }
                        },
                        'state' => function ($url, $model, $key) use ($config) {
                            if (isset($config['plugins'][$model->name])) {
                                $action = 'disable';
                                if ($config['plugins'][$model->name]['state'] == 0) {
                                    $action = 'enable';
                                }
                                return Html::a(Common::t(ucfirst($action)),'#',[
                                    'class' => 'plugins-button',
                                    'data-type' => 'state',
                                    'data-name' => $model->name,
                                    'data-action' => $action
                                ]);
                            }
                        },
                    ]
                ],
            ],
        ]); ?>


    </div>
<?php
$JS = <<<JS

var Plugins = {
    init:function(){
        $("body").on('click','.plugins-button',function() {
            var type = $(this).attr('data-type');
            Plugins[type]($(this));
        });
    }
    ,install:function(obj) {
        $.ajax({
            type: "POST",
            url: "/plugins/install",
            data: {name: obj.attr('data-name')},
            dataType: "json",
            success: function(data) {
                if(data.code == 1){
                    alert("安装成功");
                    location.href = data.url;
                }else{
                    alert("安装失败"+data.msg);
                }
            }
        })
    }
    ,upgrade:function(obj) {
        $.ajax({
            type: "POST",
            url: "/plugins/upgrade",
            data: {name: obj.attr('data-name'),version:obj.attr('data-version')},
            dataType: "json",
            success: function(data) {
                alert(data.msg);
                if(data.code == 1){
                    location.href = data.url;
                }
            }
        })
    }
    ,uninstall:function(obj) {
        $.ajax({
            type: "POST",
            url: "/plugins/uninstall",
            data: {name: obj.attr('data-name')},
            dataType: "json",
            success: function(data) {
                alert(data.msg);
                if(data.code == 1){
                    location.href = data.url;
                }
            }
        })
     }
     ,state:function(obj) {
        $.ajax({
            type: "POST",
            url: "/plugins/state",
            data: {name: obj.attr('data-name'),action:obj.attr('data-action')},
            dataType: "json",
            success: function(data) {
                alert(data.msg);
                if(data.code == 1){
                    location.href = data.url;
                }
            }
        })
    }
}


// 初始化加载插件
Plugins.init();

JS;

$this->registerJs($JS, View::POS_END) ?>