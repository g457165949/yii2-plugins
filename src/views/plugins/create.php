<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model zyh\plugins\models\Plugins */

$this->title = 'Create Plugins';
$this->params['breadcrumbs'][] = ['label' => 'Plugins', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="plugins-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>