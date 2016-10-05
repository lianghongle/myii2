<?php

namespace strong\console\controllers;

use Yii;
use strong\helpers\ConsoleHelper;
use strong\helpers\IdentityHelper;

/**
 * 查询身份证信息.
 */
class IdentityController extends \yii\console\Controller
{
    /**
     * 查询身份证信息 yii identity 422823198511163376
     */
    public function actionIndex($identity = null)
    {
        if(null === $identity){
            echo $this->ansiFormat("请输入一个合法的十八位身份证号码!!!", ConsoleHelper::FG_BLACK, ConsoleHelper::BG_YELLOW, ConsoleHelper::BOLD), PHP_EOL;
            return;
        }

        if(!IdentityHelper::check($identity)){
            echo $this->ansiFormat("{$identity} 不是一个合法的身份证号码!!!", ConsoleHelper::FG_BLACK, ConsoleHelper::BG_YELLOW, ConsoleHelper::BOLD), PHP_EOL;
            return;
        }

        //性别
        echo $this->ansiFormat("性别", ConsoleHelper::FG_CYAN), ': ' ,
            $this->ansiFormat(1 == IdentityHelper::getGender($identity) ? '女' : '男', ConsoleHelper::FG_GREEN), PHP_EOL;

        //生日
        echo $this->ansiFormat("生日", ConsoleHelper::FG_CYAN), ': ' ,
            $this->ansiFormat(IdentityHelper::getBirthday($identity), ConsoleHelper::FG_GREEN), PHP_EOL;

        //地址
        $areaInfo = IdentityHelper::getAreaInfo($identity);
        echo $this->ansiFormat("户口所在地", ConsoleHelper::FG_CYAN), ': ';

        if(isset($areaInfo['province'])){
            echo $this->ansiFormat(IdentityHelper::getAreaName($areaInfo['province']), ConsoleHelper::FG_GREEN);
        }

        if(isset($areaInfo['city'])){
            echo $this->ansiFormat(IdentityHelper::getAreaName($areaInfo['city']), ConsoleHelper::FG_GREEN);
        }

        if(isset($areaInfo['area'])){
            echo $this->ansiFormat(IdentityHelper::getAreaName($areaInfo['area']), ConsoleHelper::FG_GREEN);
        }

        echo PHP_EOL;
    }
}
