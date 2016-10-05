<?php
namespace strong\keywordFilter;

use yii;

class PhpkeywordFilter extends KeywordFilter
{
    public $phpDataPath;

    protected function load()
    {
        return require(Yii::getAlias($this->phpDataPath));
    }
}