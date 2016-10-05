<?php
namespace strong\config;

abstract class config extends \yii\base\Object
{
    /**
     * 获取配置
     * [
     *     'test' => [
     *         'test' => 'test'
     *     ]
     * ]
     * Yii::$app->get()   return ALL
     * Yii::$app->get('test')   ['test' => 'test']
     * Yii::$app->get('test.test') ['test' => 'test']
     * @param  [type] $key     [description]
     * @param  [type] $default [description]
     * @return [type]          [description]
     */
    public function get($key = null, $default = null)
    {
        if(null === $key){
            return $this->getValue($key);
        }else{
            $keyArray = explode('.', $key);

            for($i = 0; $i < count($keyArray); $i++){
                if(0 == $i){
                    $config = $this->getValue($keyArray[$i]);
                    if($default == $config && !isset($keyArray[$i + 1], $config[$keyArray[$i + 1]])){
                        return $default;
                    }
                }else{
                    if(!isset($config[$keyArray[$i]])){
                        return $default;
                    }else{
                        $config = $config[$keyArray[$i]];
                    }
                }
            }

            return $config;
        }
    }

    abstract protected function getValue($key = null);
}