<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/1/6
 * Time: 下午2:19
 */
namespace zyh\plugins\components;

use Yii;
use yii\i18n\MissingTranslationEvent;

class TranslationEventHandler
{
    public static function handleMissingTranslation(MissingTranslationEvent $event)
    {
        if($event->category != 'zh-CN'){
            $event->translatedMessage = "@MISSING: {$event->category}.{$event->message} FOR LANGUAGE {$event->language} @";
        }
    }
}
