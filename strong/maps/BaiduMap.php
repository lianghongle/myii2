<?php

namespace strong\maps;

use Yii;

class BaiduMap extends Maps
{
	protected $URI = 'http://api.map.baidu.com/place/v2/search';
	private $cachetime = 300; // 5分钟，如果不设置将不缓存（缓存相同URL的请求）
	private $category  = '美食$酒店$休闲娱乐$交通设施$生活服务';

	public $engine ='baidu';
	protected  $maps =[];

	public function init()
	{
		parent::init();
	}

	public function __construct(){
		$this->maps=Yii::$app->config->get('maps');
		$this->KEY = $this->maps['maps_all_static']['maps']['baidu'];
	}
	public function getIp(){
		$ip_arr=$this->maps['maps_all_dynamic']['maps_connect'];
		return array($ip_arr['baidu_domain'],$ip_arr['baidu_ip']);
	}
	/**
	 * 附近的信息
	 * 请求：location坐标， radius半径距离， keyword关键字, pagetoken分页信息(包括条数，当前页，访问Key)
	 * 响应：
	 * Google： id, geometry.location, name, vicinity,
	 * Baidu: street_id, location, name, address,
	 * Tencent: id, location, title, address,
	 */
	public function nearby(array $location, $page, $radius, $keyword = '', $pagetoken = '') {
		$location_type = $location [1];
		$location = $location [0];
		$pagefrom = $page;

		$cls = $this->maps ['maps_all_static'] ['client_location_types'];
		if ($cls [$location_type] != 'baidu') {
			$translate_url = "http://api.map.baidu.com/geoconv/v1/?output=json&ak=$this->KEY&coords={$location['lng']},{$location['lat']}&from=" . ($location_type ? 3 : 1);
			$tk = $this->getKey ( $translate_url );
			if (! ($translate_rs = $this->cached ( $tk ))) {
				$translate_rs = $this->request ( $translate_url );
				$translate_rs ['body'] = json_decode ( $translate_rs ['body'], 1 );
				if ($translate_rs ['body'] ['status'] != 0)
					throw new \Exception ( $translate_rs ['body'] ['message'] . ', URL:' . $translate_url );
				$this->store ( $tk, $translate_rs, $this->cachetime );

				$this->mapsRequest ( $this->engine, $pagefrom, $this->url_translate ); // 统计
			}
			$location = $translate_rs ['body'] ['result'] [0] ['y'] . ',' . $translate_rs ['body'] ['result'] [0] ['x'];
		} else {
			$location = $location ['lat'] . ',' . $location ['lng'];
		}

		$url = $this->URI . "?output=json&scope=1&ak=$this->KEY&location=$location&radius=$radius";
		if ($keyword) {
			$url .= '&query=' . urlencode ( $keyword );
			// $url.= '&tag='.urlencode(str_replace('$',',',$keyword));
		} else {
			$url .= '&query=' . urlencode ( $this->category );
		}

		if ($pagetoken) {
			if (is_numeric ( $pagetoken )) {
				$page_size = $pagetoken;
				$page_num = 0;
			} else {
				list ( $page_size, $page_num ) = explode ( ':', base64_decode ( $pagetoken ) );
			}
		} else {
			$page_size = 20;
			$page_num = 0;
		}
		$url .= "&page_size=$page_size&page_num=$page_num";

		// 用于返回的结果
		$return = array (
				'c' => 0,
				'msg' => 'ok',
				'data' => array (
						'items' => array ()
				)
		);

		// 查询并缓结果
		try {
			// echo $url,"\n\n";
			$cache_key = $this->getKey ( $url );

			if (! ($rs = $this->cached ( $cache_key ))) {
				// echo "No Cache\n";
				$rs = $this->request ( $url );
				$rs ['body'] = json_decode ( $rs ['body'], 1 );
				if ($rs ['body'] ['status'] != '0')
					throw new \Exception ( $rs ['body'] ['message'] . ', Baidu URL:' . $url );
				$this->store ( $cache_key, $rs, $this->cachetime ); // 5分钟缓存

				$this->mapsRequest ( $this->engine, $pagefrom, $this->url_request ); // 统计
			}
		} catch ( \Exception $e ) {
			$return ['c'] = 1;
			$return ['msg'] = $e->getMessage ();

			trigger_error ( $return ['msg'], E_USER_ERROR );

			return $return;
		}

		foreach ( $rs ['body'] ['results'] as $row ) {
			if (empty ( $row ['id'] ) && empty ( $row ['street_id'] ))
				continue;
			$return ['data'] ['items'] [] = array (
					'location' => $row ['location'],
					'id' => empty ( $row ['street_id'] ) ? $row ['id'] : $row ['street_id'],
					'name' => $row ['name'],
					'address' => isset ( $row ['address'] ) ? $row ['address'] : $row ['name']
			);
		}
		if (count ( $return ['data'] ['items'] ) == $page_size)
			$return ['data'] ['pagetoken'] = base64_encode ( "$page_size:" . ($page_num + 1) );

		return $return;
    }

}
