<?php

namespace strong\base;

use yii;

trait ApplicationTrait
{
    public function init()
    {
        parent::init();
        is_dir($this->getRuntimePath()) || mkdir($this->getRuntimePath());

        if($this->has('xhprof')){
            $this->xhprof->enable();
        }
    }

    public function run()
    {
        parent::run();
        $this->has('xhprof') && $this->xhprof->saveRun();
    }
}
