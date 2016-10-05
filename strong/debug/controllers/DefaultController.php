<?php

namespace strong\debug\controllers;

class DefaultController extends \yii\debug\controllers\DefaultController
{
    public function actionToolbar($tag)
    {
        $html = parent::actionToolbar($tag);
        return strtr($html, [
            '<a href="' => '<a target="_blank" href="'
        ]);
    }
}
