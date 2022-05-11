<?php

namespace app\modules\admin\behaviors;

use yii\base\Behavior;

class MyBehavior extends Behavior
{
    public function init()
    {
        parent::init();
        
    }
    
    public function test()
    {
        return 'test';
    }
}