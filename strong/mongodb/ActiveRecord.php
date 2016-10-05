<?php
namespace strong\mongodb;

use yii\data\Pagination;

class ActiveRecord extends \yii\mongodb\ActiveRecord
{
    /**
     * 分页
     *
     * @param int $page             当前页
     * @param int $pageSize         每页数据个数
     * @param array $condition      where
     * @param array $order          ['id' => SORT_ASC, 'name' => SORT_DESC]
     * @return array
     */
    public static function getPagination($page = 1, $pageSize = 10, $condition = [], $order = ['_id' => SORT_DESC])
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