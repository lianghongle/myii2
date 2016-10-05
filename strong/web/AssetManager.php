<?php

namespace strong\web;

use Yii;
use strong\helpers\FileHelper;

class AssetManager extends \yii\web\AssetManager
{
    public function init()
    {
        $this->basePath = Yii::getAlias($this->basePath);
        is_dir($this->basePath) OR FileHelper::createDirectory($this->basePath, $this->dirMode, true);
        parent::init();
    }
}
