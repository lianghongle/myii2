<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace strong\console\controllers;

use Yii;
use yii\console\Controller;
use yii\caching\Cache;
use yii\helpers\ConsoleHelper;
use yii\console\Exception;

/**
 * Allows you to flush cache.
 *
 * see list of available components to flush:
 *
 *     yii cache
 *
 * flush particular components specified by their names:
 *
 *     yii cache/flush first second third
 *
 * flush all cache components that can be found in the system
 *
 *     yii cache/flush-all
 *
 * Note that the command uses cache components defined in your console application configuration file. If components
 * configured are different from web application, web application cache won't be cleared. In order to fix it please
 * duplicate web application cache components in console config. You can use any component names.
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @author Mark Jebri <mark.github@yandex.ru>
 * @since 2.0
 */
class CacheController extends \yii\console\controllers\CacheController
{
    /**
     * 删除指定缓存组件过期的KEY.
     *
     * ~~~
     * # gc caches specified by their id: "first", "second", "third"
     * yii cache/gc first second third
     * ~~~
     *
     */
    public function actionGc()
    {
        $cachesInput = func_get_args();

        if (empty($cachesInput)) {
            throw new Exception("You should specify cache components names");
        }

        $caches = $this->findNeedGcCaches($cachesInput);
        $cachesInfo = [];

        $foundCaches = array_keys($caches);
        $notFoundCaches = array_diff($cachesInput, array_keys($caches));

        if ($notFoundCaches) {
            $this->notifyNotFoundCaches($notFoundCaches);
        }

        if (!$foundCaches) {
            $this->notifyNoCachesFound();
            return static::EXIT_CODE_NORMAL;
        }

        if (!$this->confirmFlush($foundCaches)) {
            return static::EXIT_CODE_NORMAL;
        }

        foreach ($caches as $name => $class) {
            $cachesInfo[] = [
                'name' => $name,
                'class' => $class,
                'is_gc' =>  Yii::$app->get($name)->gc(true),
            ];
        }

        $this->notifyGced($cachesInfo);
    }

    /**
     * 删除全部缓存组件过期的KEY.
     */
    public function actionGcAll()
    {
        $caches = $this->findCaches();
        $cachesInfo = [];

        if (empty($caches)) {
            $this->notifyNoCachesFound();
            return static::EXIT_CODE_NORMAL;
        }

        foreach ($caches as $name => $class) {
            $cachesInfo[] = [
                'name' => $name,
                'class' => $class,
                'is_gced' =>  Yii::$app->get($name)->gc(true),
            ];
        }

        $this->notifyFlushed($cachesInfo);
    }




        /**
     * Notifies user that given caches are found and can be flushed.
     * @param array $caches array of cache component classes
     */
    private function notifyCachesCanBeFlushed($caches)
    {
        $this->stdout("The following caches were found in the system:\n\n", ConsoleHelper::FG_YELLOW);

        foreach ($caches as $name => $class) {
            $this->stdout("\t* $name ($class)\n", ConsoleHelper::FG_GREEN);
        }

        $this->stdout("\n");
    }

    /**
     * Notifies user that there was not found any cache in the system.
     */
    private function notifyNoCachesFound()
    {
        $this->stdout("No cache components were found in the system.\n", ConsoleHelper::FG_RED);
    }

    /**
     * Notifies user that given cache components were not found in the system.
     * @param array $cachesNames
     */
    private function notifyNotFoundCaches($cachesNames)
    {
        $this->stdout("The following cache components were NOT found:\n\n", ConsoleHelper::FG_RED);

        foreach ($cachesNames as $name) {
            $this->stdout("\t * $name \n", ConsoleHelper::FG_GREEN);
        }

        $this->stdout("\n");
    }

    /**
     *
     * @param array $caches
     */
    private function notifyFlushed($caches)
    {
        $this->stdout("The following cache components were processed:\n\n", ConsoleHelper::FG_YELLOW);

        foreach ($caches as $cache) {
            $this->stdout("\t* " . $cache['name'] ." (" . $cache['class'] . ")", ConsoleHelper::FG_GREEN);
            $this->stdout("\n");
        }

        $this->stdout("\n");
    }

    /**
     * Prompts user with confirmation if caches should be flushed.
     * @param array $cachesNames
     * @return boolean
     */
    private function confirmFlush($cachesNames)
    {
        $this->stdout("The following cache components will be flushed:\n\n", ConsoleHelper::FG_YELLOW);

        foreach ($cachesNames as $name) {
            $this->stdout("\t * $name \n", ConsoleHelper::FG_GREEN);
        }

        return $this->confirm("\nFlush above cache components?");
    }

    /**
     * Returns array of caches in the system, keys are cache components names, values are class names.
     * @param array $cachesNames caches to be found
     * @return array
     */
    private function findNeedGcCaches(array $cachesNames = [])
    {
        $caches = [];
        $components = Yii::$app->getComponents();
        $findAll = empty($cachesNames);

        foreach ($components as $name => $component) {
            if (!$findAll && !in_array($name, $cachesNames)) {
                continue;
            }

            if ($component instanceof Cache) {
                $caches[$name] = get_class($component);
            } elseif (is_array($component) && isset($component['class']) && $this->isNeedGcCacheClass($component['class'])) {
                $caches[$name] = $component['class'];
            } elseif (is_string($component) && $this->isNeedGcCacheClass($component)) {
                $caches[$name] = $component;
            }
        }

        return $caches;
    }

    /**
     * Checks if given class is a Cache class.
     * @param string $className class name.
     * @return boolean
     */
    private function isNeedGcCacheClass($className)
    {
        return is_subclass_of($className, Cache::className()) && method_exists($className, 'gc');
    }

}
