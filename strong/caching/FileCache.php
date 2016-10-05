<?php

namespace strong\caching;

use strong\helpers\FileHelper;

/**
 * 扩展FileCache类
 *
 * @author hugh
 */
class FileCache extends \yii\caching\FileCache
{

    protected function getValue($key)
    {
        $cacheFile = $this->getCacheFile($key);

        if(@filemtime($cacheFile) > time()){
            return FileHelper::getContents($cacheFile, false);
        }else{
            is_file($cacheFile) AND @unlink($cacheFile);
            return false;
        }
    }

    protected function setValue($key, $value, $duration)
    {
        $cacheFile = $this->getCacheFile($key);

        0 < $this->directoryLevel AND @FileHelper::createDirectory(dirname($cacheFile), $this->dirMode, true);

        if(false !== FileHelper::putContents($cacheFile, $value, false, false, $this->fileMode)){
            return @touch($cacheFile, (0 < $duration ? $duration : 31536000) + time());
        }else{
            return false;
        }
    }
}
