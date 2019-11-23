<?php

use yii\db\Migration;

/**
 * Handles the creation of table `plugins`.
 */
class m191123_050429_create_plugins_table extends Migration
{
    public $tableName;


    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $this->tableName = $this->db->tablePrefix.'plugins';
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'uid' => $this->string(),
            'title' => $this->string()->notNull(),
            'name' => $this->string()->notNull(),
            'author' => $this->string()->notNull(),
            'intro' => $this->text(),
            'version' => $this->string()->notNull(),
            'url' => $this->string()->notNull(),
            'state' => $this->tinyInteger(),
            'category_id' => $this->tinyInteger(),
            'create_time' => $this->integer(),
            'update_time' => $this->integer()
        ]);


        $this->insert($this->tableName, [
            'uid' => 1,
            'title' => '菜单',
            'name' => 'menu',
            'author' => 'zhangyonghui',
            'intro' => '菜单插件',
            'version' => '1.0.0',
            'url' => 'menu/index',
            'state' => 1,
            'create_time' => time(),
            'update_time' => time(),
            'category_id' => 1,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
