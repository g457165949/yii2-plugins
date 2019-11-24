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
     *              $handlerMethodName
     *          ],
     *          // 或者
     *          $eventName => [
     *              //[方法名,方法参数,是否继续执行(true/false)]
     *              [$handlerMethodName , $handlerMethodParams , $handler]
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
                        $data = isset($handler[1]) ? $handler[1] : null;
                        $append = isset($handler[2]) ? $handler[2] : null;
                        Event::on($className, $eventName, $handler[0], $data, $append);
                    } else if (is_callable($handler)) {
                        Event::on($className, $eventName, $handler);
                    }
                }
            }
        }
    }
}