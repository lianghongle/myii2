<?php
namespace strong\db;

use yii;
use ArrayObject;
use yii\validators\Validator;
use yii\data\Pagination;

class ActiveRecord extends \yii\db\ActiveRecord
{
    /**
     * 填充一个模型;
     * @param  [type] $row [description]
     * @return [type]      [description]
     */
//    public static function populate($row)
//    {
//        $model = static::instantiate([]);
//        static::populateRecord($model, $row);
//        return $model;
//
//        $class = $this->modelClass;
//        $model = $class::instantiate($result);
//        $class::populateRecord($model, $result);
//        if (!empty($this->with)) {
//            $models = [$model];
//            $this->findWith($this->with, $models);
//            $model = $models[0];
//        }
//        $model->afterFind();
//    }

    /**
     * static::validateAttribute($attributes)->hasErrors()
     *
     * 验证属性的正确性
     *
     * @param  [type] $attributes [description]
     * @return [type]             [description]
     */
    public static function validateAttribute(array $attributes)
    {
        $model = static::instantiate([]);
        $model->attributes = $attributes;
        $model->validate(array_keys($attributes));
        return $model;
    }

    /**
     * 分页
     *
     * @param int $page             当前页
     * @param int $pageSize         每页数据个数
     * @param array $condition      where
     * @param array $order          ['id' => SORT_ASC, 'name' => SORT_DESC]
     * @return array
     */
    public static function getPagination($page = 1, $pageSize = 10, $condition = [], $order = ['id' => SORT_DESC])
    {
        $page = $page < 1 ? 1 : $page;
        $offset = ($page - 1) * $pageSize;

        $data = static::find()->where($condition)->orderBy($order);
        $pages = new Pagination([
            'page' => $page,
            'totalCount' => $data->count(),
            'pageSize' => $pageSize
        ]);

        $pagination = $data->offset($offset)->limit($pages->limit)->asArray()->all();

        return $pagination;
    }
}