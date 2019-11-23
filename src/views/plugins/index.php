<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\web\View;
use zyh\plugins\components\Common;

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

        <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
//        'filterModel' => $searchModel,
            'columns' => [
//            ['class' => 'yii\grid\SerialColumn'],

                'id',
                'uid',
                'title',
                'name',
                'author',
                'intro:ntext',
                'version',
//            'url:url',
//            'state',
                'category_id',
                'create_time:datetime',
                //'update_time:datetime',

                [
                    'class' => 'yii\grid\ActionColumn',
                    'header' => '操作',
                    'template' => '{view} {install} {uninstall} {upgrade} {state}',
                    'buttons' => [
                        'view' => function ($url, $model, $key) {
                            return Html::a('详情', ['view', "id" => $model->id]);
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
                            if (isset($config['plugins'][$model->name])) {
                                return Html::a(Common::T('Uninstall'), "#", [
                                    'class' => 'plugins-button',
                                    'data-type' => 'uninstall',
                                    'data-name' => $model->name
                                ]);
                            }
                        },
                        'upgrade' => function ($url, $model, $key) use ($config) {
                            $info = ArrayHelper::getValue($config, "plugins.{$model->name}");
                            if ($info && $info['version'] != $model->version) {
                                return Html::a(Common::T('Upgrade'));
                            }
                        },
                        'state' => function ($url, $model, $key) use ($config) {
                            if (isset($config['plugins'][$model->name])) {
                                $txt = '禁用';
                                if ($config['plugins'][$model->name]['state'] == 0) {
                                    $txt = '启用';
                                }
                                return Html::a($txt);
                            }
                        },
                    ]
                ],
            ],
        ]); ?>


    </div>
<?php
$statusJs = <<<JS



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
}


// 初始化加载插件
Plugins.init();

JS;

$this->registerJs($statusJs, View::POS_END) ?>