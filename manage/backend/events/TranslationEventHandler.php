<?php
//翻译事件回调方法

namespace app\events;

use yii\i18n\MissingTranslationEvent;

class TranslationEventHandler
{
    public static function handleMissingTranslation(MissingTranslationEvent $event)
    {
        //直接提示
        $event->translatedMessage = $event->message;
        
        //纠错
        //$event->translatedMessage = "@MISSING: {$event->category}.{$event->message} FOR LANGUAGE {$event->language} @";
    }
}

