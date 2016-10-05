<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace strong\console;

use Yii;
use strong\base\ApplicationTrait;

class Application extends \yii\console\Application
{
	use ApplicationTrait;

    public function coreCommands()
    {
        return array_merge(parent::coreCommands(), [
            'opcache' => 'strong\console\controllers\OpcacheController',
            'init' => 'strong\console\controllers\InitController',
            'identity' => 'strong\console\controllers\IdentityController',
            'cache' => 'strong\console\controllers\CacheController',
        ]);
    }
}
