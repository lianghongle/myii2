<?php
namespace strong\keywordFilter;

use yii;

abstract class keywordFilter extends \yii\base\Object
{
    protected $_keywordList = [];

    public $defaultLevel = 1;

    public function init()
    {
        parent::init();
        $this->_keywordList = $this->load();
    }

    public function check($string, $level = null)
    {
        $level = null === $level ? $this->defaultLevel : intval($level);

        foreach ($this->_keywordList as $_keyword => $_level) {
            if($_level > $level){continue;}

            if(false !== stripos($string, $_keyword)){
                return ['c' => 1, 'keyword' => [$_keyword]];
            }
        }

        return ['c' => 0];
    }

    abstract protected function load();
}