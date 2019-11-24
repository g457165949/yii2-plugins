<?php

namespace zyh\plugins\models;

use Yii;
use yii\helpers\ArrayHelper;
use zyh\plugins\components\Common;

/**
 * This is the model class for table "{{%plugins}}".
 *
 * @property int $id
 * @property string|null $uid
 * @property string $title
 * @property string $name
 * @property string $author
 * @property string|null $intro
 * @property string $version
 * @property string $url
 * @property int|null $state
 * @property int|null $category_id
 * @property int|null $create_time
 * @property int|null $update_time
 */
class Plugins extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugins}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'name', 'author', 'version', 'url'], 'required'],
            [['intro'], 'string'],
            [['state', 'category_id', 'create_time', 'update_time'], 'integer'],
            [['uid', 'title', 'name', 'author', 'version', 'url'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uid' => 'Uid',
            'title' => Common::T('Title'),
            'name' => Common::T('Name'),
            'author' => Common::T('Author'),
            'intro' => Common::T('Intro'),
            'version' => Common::T('Version'),
            'url' => Common::T('Url'),
            'state' => Common::T('State'),
            'category_id' => 'Category ID',
            'create_time' => Common::T('Create Time'),
            'update_time' => 'Update Time',
        ];
    }

    public static function dropDownList($key, $value = null)
    {

        $params = [
            'category_id' => [
                1 => '模块',
                2 => '功能',
            ],
            'state' => [
                0 => '禁用',
                1 => '启用'
            ]
        ];

        return ArrayHelper::getValue($params, isset($value) ? "$key.$value" : "$key");
    }
}