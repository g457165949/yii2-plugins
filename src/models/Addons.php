<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/1/18
 * Time: 下午11:34
 */
namespace zyh\plugins\models;

use Yii;

/**
 * This is the model class for table "{{%_addons}}".
 *
 * @property int $id
 * @property string $name 插件名称
 * @property string $title 插件标题
 * @property string $author 插件作者
 * @property float $price 价格
 * @property int $category_id 插件分类
 * @property string $intro 插件介绍
 * @property string $website 网站地址
 * @property string $version 版本
 * @property string $type 类型
 * @property int $state 状态
 * @property string $url 插件路由地址
 * @property int $createtime
 */
class Addons extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%_addons}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'category_id'], 'required'],
            [['id', 'category_id', 'state', 'createtime'], 'integer'],
            [['intro', 'type'], 'string'],
            [['price'],'number'],
            [['name', 'author', 'version'], 'string', 'max' => 20],
            [['title', 'website', 'url'], 'string', 'max' => 100],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '插件名称',
            'title' => '插件标题',
            'intro' => '插件介绍',
            'website' => '网站地址',
            'version' => '版本',
            'state' => '状态',
            'url' => '插件路由地址',
            'createtime' => 'Createtime',
        ];
    }
}