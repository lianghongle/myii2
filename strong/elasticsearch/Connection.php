<?php
namespace strong\elasticsearch;

use Yii;

class Connection extends \yii\elasticsearch\Connection
{
    public function orderBuilder($sort)
    {
        $sorting = [];
        foreach ($sort as $field => $type) {
            if(is_array($type)){
                if('RADIUS' == $type['type']){
                    $sorting['_geo_distance'] = [
                        "location.{$field}" => [
                            'lat' => $type['location']['lat'],
                            'lon' => $type['location']['lon']
                        ],
                        'order' => $type['order'],
                        'unit' => 'km',
                        'mode' => 'min',
                        'distance_type' => 'sloppy_arc'
                    ];
                }
            }else{
                $sorting[$field] = ['order' => $type];
            }
        }
        return $sorting;
    }

    public function queryBuilder($where)
    {
        $query = [];
        foreach ($where as $field => $select) {
            foreach ($select as $operator => $value) {
                //不等于（<>）
                if('NEQ' == $operator){
                    $query['query']['bool']['must_not'][] = ['term' => [$field => $value]];
                }
                //大于（>）
                elseif('GT' == $operator){
                    $query['query']['bool']['must'][] = ['range' => [$field => ['gt' => $value]]];
                }
                //大于等于（>=）
                elseif('EGT' == $operator){
                    $query['query']['bool']['must'][] = ['range' => [$field => ['gte' => $value]]];
                }
                //小于（<）
                elseif('LT' == $operator){
                    $query['query']['bool']['must'][] = ['range' => [$field => ['lt' => $value]]];
                }
                //小于等于（<=）
                elseif('ELT' == $operator){
                    $query['query']['bool']['must'][] = ['range' => [$field => ['lte' => $value]]];
                }
                //范围
                elseif('BETWEEN' == $operator){
                    $query['query']['bool']['must'][] = ['range' => [$field => ['gte' => $value[0], 'lte' => $value[1]]]];
                }
                //IN
                elseif('NEQ' == $operator){
                    $query['query']['bool']['must'][] = ['terms' => [$field => $value]];
                }
                //NOTIN
                elseif('NOTIN' == $operator){
                    $query['query']['bool']['must_not'][] = ['terms' => [$field => $value]];
                }
                //距离
                elseif('RADIUS' == $operator){
                    $query['filter']['geo_distance'] = [
                        'distance' => ($value['distance'] / 1000) . 'km',
                        'location' => [
                            'lat' => $value['location']['lat'],
                            'lon' => $value['location']['lon']
                        ],
                    ];
                }
            }
        }

        if(isset($query['filter'])){
            $query = ['filtered' => $query];
        }else{
            $query = $query['query'];
        }
        return $query;
    }
}
