<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/1/22
 * Time: 下午6:16
 */

namespace zyh\plugins\components;


abstract class Plugin
{
    public $identify;

    public function __construct()
    {
        $class = get_class($this);
        $reflection = new \ReflectionClass($class);
        $this->pluginDir = dirname($reflection->getFileName());
    }

    protected function hooks(){
        return [];
    }

    abstract public function install();

    abstract public function uninstall();
}