<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/1/22
 * Time: 下午5:04
 */


use app\plugins\menu\MenuAsset;
use yii\helpers\Html;
use yii\web\View;
use zyh\plugins\components\Common; ?>
show views menu index.php

<br>
省去当前路径：<?=Common::t('test1');?>
<br>
<?=Yii::t('menu','test1');?>
<br>
<?=Yii::t('menu/test','test');?>
<br>

<?php
$menuJs = MenuAsset::register($this)->baseUrl.'/js/menu.js';
echo 'js路径：'.$menuJs;
$this->registerJsFile($menuJs,[
     'depends' => [
            app\assets\AppAsset::className(),
        ],
    //可选参数，定义当前js文件的出现位置，默认位置为View::POS_END
    'position' => View::POS_END,
]);
?>

<?=Html::img(MenuAsset::register($this)->baseUrl.'/img/图片1.jpg',['width' => '300px']);?>
<br>
<?="图片路径".MenuAsset::register($this)->baseUrl.'/img/图片1.jpg'?>

