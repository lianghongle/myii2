<?php

namespace strong\IdGenerator;

abstract class IdGenerator extends \yii\base\Object
{
    abstract public function getId();
}
