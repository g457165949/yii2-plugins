<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/1/23
 * Time: 下午6:04
 */

namespace zyh\plugins\components;


use yii\base\Component;
use yii\base\Event;

class EventManager extends Component
{
    /**
     *
     * an array with structure: [
     *      $eventSenderClassName => [
     *          $eventName => [
     *              [$handlerClassName, $handlerMethodName]
     *          ]
     *      ]
     * ]
     *
     * @var array events settings
     */
    public $events = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        // var_dump($this->events);
        $this->attachEvents($this->events);
    }

    /**
     * @param $eventConfig
     */
    public function attachEvents($eventConfig)
    {
        foreach ($eventConfig as $className => $events) {
            foreach ($events as $eventName => $handlers) {
                foreach ($handlers as $handler) {
                    if (is_array($handler) && is_callable($handler[0])) {
                        $data = isset($handler[1]) ? array_pop($handler) : null;
                        $append = isset($handler[2]) ? array_pop($handler) : null;
                        Event::on($className, $eventName, $handler[0], $data, $append);
                    } else if (is_callable($handler)) {
                        Event::on($className, $eventName, $handler);
                    }
                }
            }
        }
    }
}