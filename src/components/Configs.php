<?php

namespace zyh\plugins\components;

use Codeception\Exception\ConfigurationException;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Connection;
use yii\caching\Cache;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

/**
 * Configs
 * Used for configure some value. To set config you can use [[\yii\base\Application::$params]]
 * 
 * ~~~
 * return [
 *     
 *     'zyh.plugins.configs' => [
 *         'pluginRoot' => '@app/plugins',  // 插件物理路径
 *         'pluginNamespace' => 'app\plugins',  // 插件命名空间
 *         'pluginFile' => 'info.ini',  //json or xml 插件本地配置信息
 *         'db' => 'db',
 *         'pluginsTable' => 'plugins',
 *
 *     ]
 * ];
 * ~~~
 * 
 * or use [[\Yii::$container]]
 * 
 * ~~~
 * Yii::$container->set('zyh\plugins\components\Configs',[
 *     'db' => 'customDb',
 *     'pluginsTable' => 'plugins',
 * ]);
 * ~~~
 *
 */
class Configs extends \yii\base\BaseObject
{
    const CACHE_TAG = 'zyh.plugins.configs';
    /**
     * @var Connection Database connection.
     */
    public $db = 'db';

    /**
     * @var Cache Cache component.
     */
    public $cache = 'cache';

    /**
     * @var integer 缓存更新时间
     */
    public $cacheDuration = 0;

    /**
     * @var string 插件表名
     */
    public $pluginsTable = '{{%plugins}}';

    /**
     * 插件路径
     * @var string
     */
    public $pluginRoot = '@app/plugins';

    /**
     * 插件命名
     * @var string
     */
    public $pluginNamespace = 'app\plugins';

    /**
     * 插件文件
     * @var string
     */
    public $pluginFile = 'info.ini';

    /**
     * 插件下载地址
     * @var string
     */
    public $pluginDownloadUrl = '';

    /**
     * 插件路由规则
     * @var array
     */
    public $pluginUrlRule = ['class' => 'zyh\plugins\components\PluginUrlRule'];

    /**
     * 插件范围
     * @var array
     */
    public $pluginScope = [];


    /**
     * @var array 
     */
    public $options;

    /**
     * @var self Instance of self
     */
    private static $_instance;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if(!$this->pluginRoot || !$this->pluginNamespace){
            throw new InvalidConfigException("pluginRoot and pluginNamespace must be set.");
        }

        if ($this->db !== null && !($this->db instanceof Connection)) {
            if (is_string($this->db) && strpos($this->db, '\\') === false) {
                $this->db = Yii::$app->get($this->db, false);
            } else {
                $this->db = Yii::createObject($this->db);
            }
        }

        if ($this->cache !== null && !($this->cache instanceof Cache)) {
            if (is_string($this->cache) && strpos($this->cache, '\\') === false) {
                $this->cache = Yii::$app->get($this->cache, false);
            } else {
                $this->cache = Yii::createObject($this->cache);
            }
        }
        parent::init();
    }

    /**
     * Create instance of self
     * @return static
     */
    public static function instance()
    {
        if (self::$_instance === null) {
            $type = ArrayHelper::getValue(Yii::$app->params, self::CACHE_TAG, []);
            if (is_array($type) && !isset($type['class'])) {
                $type['class'] = static::className();
            }

            return self::$_instance = Yii::createObject($type);
        }

        return self::$_instance;
    }

    public static function __callStatic($name, $arguments)
    {
        $instance = static::instance();
        if ($instance->hasProperty($name)) {
            return $instance->$name;
        } else {
            if (count($arguments)) {
                $instance->options[$name] = reset($arguments);
            } else {
                return array_key_exists($name, $instance->options) ? $instance->options[$name] : null;
            }
        }
    }

    /**
     * @return Connection
     */
    public static function db()
    {
        return static::instance()->db;
    }

    /**
     * @return Cache
     */
    public static function cache()
    {
        return static::instance()->cache;
    }

    /**
     * @return integer
     */
    public static function cacheDuration()
    {
        return static::instance()->cacheDuration;
    }

    /**
     * @return string
     */
    public static function pluginsTable()
    {
        return static::instance()->pluginsTable;
    }
}
