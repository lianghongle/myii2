<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace strong\console\controllers;

use Yii;
use strong\helpers\ConsoleHelper;

/**
 * opcache控制台管理.
 */
class OpcacheController extends \yii\console\Controller
{
    public function beforeAction($action)
    {
        if(parent::beforeAction($action)){
            if(extension_loaded('Zend OPcache')){
                return true;
            }else{
                echo $this->ansiFormat('Zend OPcache扩展未开启.', ConsoleHelper::FG_BLACK, ConsoleHelper::BG_YELLOW, ConsoleHelper::BOLD), PHP_EOL;
            }
        }

        return false;
    }

    /**
     * opcache状态.
     */
    public function actionStatus()
    {
        var_dump(opcache_get_status());
    }

    /**
     * 不运行编译并换成php脚本.
     */
    public function actionCompile()
    {
        //opcache_compile_file
    }

    /**
     * 获取opcache的config.
     */
    public function actionConfig()
    {
        $config = opcache_get_configuration();
        $options = $config['directives'];
        $options['version'] = $config['version']['version'];
        $options['opcache_product_name'] = $config['version']['opcache_product_name'];
        $options['blacklist'] = implode(',', $config['blacklist']);

        foreach ($options as $key => $value) {
            $value = is_bool($value) ? ($value ? 'true' : 'false') : $value;
            echo $this->ansiFormat("{$key}", ConsoleHelper::FG_YELLOW), ':', $this->ansiFormat("{$value}", ConsoleHelper::FG_CYAN), PHP_EOL;
        }
    }

    /**
     * 废除脚本缓存.
     */
    public function actionInvalidate()
    {
        //opcache_invalidate
    }

    /**
     * 重置字节码缓存.
     */
    public function actionReset()
    {
        if(opcache_reset()){
            echo "重置成功, 所有的脚本将会重新载入并且在下次被点击的时候重新解析." , PHP_EOL;
        }else{
            echo "重置失败" , PHP_EOL;
        }
    }

    public function actionFileCacheStatus()
    {
        $status = opcache_get_status();

        $fileStatus = isset($status['scripts']) ? $status['scripts'] : array();
        foreach ($fileStatus as $key => $value) {
            echo $this->ansiFormat("{$value['full_path']}", ConsoleHelper::FG_GREEN), PHP_EOL;
            echo $this->ansiFormat("hits", ConsoleHelper::FG_YELLOW), ': ', $this->ansiFormat("{$value['hits']}", ConsoleHelper::FG_CYAN), ', ';
            echo $this->ansiFormat("memory", ConsoleHelper::FG_YELLOW), ': ', $this->ansiFormat("{$value['memory_consumption']}", ConsoleHelper::FG_CYAN), ', ';
            echo $this->ansiFormat("last_used", ConsoleHelper::FG_YELLOW), ': ', $this->ansiFormat(date('Y-m-d H:i:s', $value['last_used_timestamp']), ConsoleHelper::FG_CYAN), ', ';
            echo $this->ansiFormat("create", ConsoleHelper::FG_YELLOW), ': ', $this->ansiFormat(date('Y-m-d H:i:s', $value['timestamp']), ConsoleHelper::FG_CYAN);
            echo PHP_EOL, PHP_EOL;
        }

        $opcacheStatus = isset($status['opcache_statistics']) ? $status['opcache_statistics'] : array();
        if(!empty($opcacheStatus)){
            echo $this->ansiFormat("文件总数", ConsoleHelper::FG_GREEN), ': ' , $this->ansiFormat("{$opcacheStatus['num_cached_scripts']}", ConsoleHelper::FG_CYAN), ', ';
            echo $this->ansiFormat("key总数", ConsoleHelper::FG_GREEN), ': ' , $this->ansiFormat("{$opcacheStatus['num_cached_keys']}", ConsoleHelper::FG_CYAN), ', ';
            echo $this->ansiFormat("命中", ConsoleHelper::FG_GREEN), ': ' , $this->ansiFormat("{$opcacheStatus['hits']}", ConsoleHelper::FG_CYAN);
            echo PHP_EOL, PHP_EOL;
        }
    }
}
