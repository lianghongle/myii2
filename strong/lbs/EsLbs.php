<?php
namespace strong\lbs;

use yii;
use yii\di\Instance;
use strong\helpers\ArrayHelper;
use strong\helpers\JsonHelper;
use yii\elasticsearch\Connection;

class EsLbs extends Lbs
{
    public $db;

    public $index;

    public $type;

    public function init()
    {
        parent::init();

        $this->db = Instance::ensure($this->db, Connection::className());
    }

    protected function getUserItems(array $uids)
    {
        $list = $this->db->get(
            [$this->index, $this->type, '_mget'],
            [],
            JsonHelper::encode(['ids' => array_values($uids)])
        );

        $list = isset($list['docs']) ? $list['docs'] : [];
        $list = array_filter($list, function($item){
            return isset($item['found'], $item['_source']) && $item['found'];
        });

        return $this->formatResponseList($list);
    }

    protected function uploadItems(array $list)
    {
        $list = array_map(function($item){
            $location = $this->analyseLocation($item['location']);
            $item['location'] = ['lat' => $location['lat'], 'lon' => $location['lng']];
            return $item;
        }, $list);

        $body = '';
        foreach ($list as $item) {
            $body .= JsonHelper::encode(["update" => array("_id" => $item['uid'])]) . "\n";
            unset($item['uid']);
            $body .= JsonHelper::encode(["doc" => $item, 'doc_as_upsert' => true]) . "\n";
        }

        $response = $this->db->post([$this->index, $this->type, '_bulk'], [], $body);
        return isset($response['errors']) && false === $response['errors'];
    }

    protected function searchItems($location, array $where, array $sort, $offset, $limit)
    {
        $location = $this->analyseLocation($location);

        $query = [
            'from' => $offset,
            'size' => $limit,
            'query' => []
        ];

        //组合sort
        if(isset($sort['updated'])){
            $query['sort']['updated'] = ['order' => $sort['updated']];
        }
        if(isset($sort['radius'])){
            $query['sort']['_geo_distance'] = [
                'location.location' => ['lat' => $location['lat'], 'lon' => $location['lng']],
                'order' => $sort['location'],
                'unit' => 'km',
                'mode' => 'min',
                'distance_type' => 'sloppy_arc'
            ];
        }


        $query = [];
        foreach ($where as $field => $select) {
            foreach ($select as $operator => $value) {
                //不等于（<>）
                if('neq' == $operator){
                    $query['filtered']['query']['bool']['must_not'][] = ['term' => [$field => $value]];
                }
                //大于（>）
                elseif('gt' == $operator){
                    $query['filtered']['query']['bool']['must'][] = ['range' => [$field => ['gt' => $value]]];
                }
                //大于等于（>=）
                elseif('egt' == $operator){
                    $query['filtered']['query']['bool']['must'][] = ['range' => [$field => ['gte' => $value]]];
                }
                //小于（<）
                elseif('lt' == $operator){
                    $query['filtered']['query']['bool']['must'][] = ['range' => [$field => ['lt' => $value]]];
                }
                //小于等于（<=）
                elseif('elt' == $operator){
                    $query['filtered']['query']['bool']['must'][] = ['range' => [$field => ['lte' => $value]]];
                }
                //范围
                elseif('between' == $operator){
                    $query['filtered']['query']['bool']['must'][] = ['range' => [$field => ['gte' => $value[0], 'lte' => $value[1]]]];
                }
                //IN
                elseif('in' == $operator){
                    $query['filtered']['query']['bool']['must'][] = ['terms' => [$field => $value]];
                }
                //NOTIN
                elseif('notin' == $operator){
                    $query['filtered']['query']['bool']['must_not'][] = ['terms' => [$field => $value]];
                }
                //距离
                elseif('radius' == $operator){
                    $query['filtered']['filter']['geo_distance'] = [
                        'distance' => ($value / 1000) . 'km',
                        'location' => [
                            'lat' => $location['lat'],
                            'lon' => $location['lng']
                        ],
                    ];
                }
            }
        }

        $list = $this->db->get(
            [$this->index, $this->type, '_search'],
            [],
            JsonHelper::encode($query)
        );

        $list = isset($list['hits']['hits']) ? $list['hits']['hits'] : [];
        return $this->formatResponseList($list);
    }

    protected function deleteItems($uids)
    {
        $body = '';
        foreach ($uids as $uid) {
            $body .= JsonHelper::encode(["delete" => ["_id" => $uid]]) . "\n";
        }
        $response = $this->db->post([$this->index, $this->type, '_bulk'], [], $body);
        return isset($response['errors']) && false === $response['errors'];
    }

    protected function formatResponseList($list)
    {
        return array_values(array_map(function($item){
            $item['_source']['uid'] = $item['_id'];
            $item['_source']['location'] = $item['_source']['location']['lon']
                                    . ",{$item['_source']['location']['lat']}";
            return $item['_source'];
        }, $list));
    }
}