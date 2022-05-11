<?php

namespace app\behaviors;

use yii\base\Behavior;

class MyBehavior extends Behavior
{
    public function init()
    {
        parent::init();
        
        print_r('1.test init ');
    }
    
    public function test()
    {
        return '2.test';
    }
}