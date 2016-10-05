<?php

namespace strong\maps;

use Yii;

class TencentMap extends Maps
{
	protected $URI = 'http://apis.map.qq.com/ws/place/v1/search';

	private $cachetime = 300; // 5分钟，如果不设置将不缓存（缓存相同URL的请求）

	# form http://lbs.qq.com/webservice_v1/guide-appendix.html
	private $category = ''; // 没有传入关键字是，列出美食类的商家

	public $engine ='tencent';
	protected  $maps =[];

	public function init()
	{
		parent::init();
	}

	public function __construct(){
		$this->maps=Yii::$app->config->get('maps');
		$this->KEY = $this->maps['maps_all_static']['maps']['tencent'];
	}
	public function getIp(){
		$ip_arr=$this->maps['maps_all_dynamic']['maps_connect'];
		return array($ip_arr['tencent_domain'],$ip_arr['tencent_ip']);
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
		if ($cls [$location_type] == 'baidu' || $cls [$location_type] == 'gps') {
			$location_type = $cls [$location_type] == 'gps' ? 1 : 3;
			$translate_url = "http://apis.map.qq.com/ws/coord/v1/translate?locations={$location['lat']},{$location['lng']}&key=$this->KEY&type=$location_type";
			$tk = $this->getKey ( $translate_url );
			if (! ($translate_rs = $this->cached ( $tk ))) {
				$translate_rs = $this->request ( $translate_url );
				$translate_rs ['body'] = json_decode ( $translate_rs ['body'], 1 );
				if ($translate_rs ['body'] ['status'] != 0)
					throw new \Exception ( $translate_rs ['body'] ['message'] . ', URL:' . $translate_url );
				$this->store ( $tk, $translate_rs, $this->cachetime );

				$this->mapsRequest ( $this->engine, $pagefrom, $this->url_translate ); // 统计
			}
			$location = $translate_rs ['body'] ['locations'] [0] ['lat'] . ',' . $translate_rs ['body'] ['locations'] [0] ['lng'];
		} else {
			$location = $location ['lat'] . ',' . $location ['lng'];
		}

		$url = $this->URI . "?output=json&key=$this->KEY&boundary=nearby($location,$radius)";
		if ($keyword) {
			$url .= '&keyword=' . urlencode ( $keyword );
		} else {
			$url .= '&keyword=' . urlencode ( $this->category );
		}

		if ($pagetoken) {
			if (is_numeric ( $pagetoken )) {
				$page_size = $pagetoken;
				$page_index = 1;
			} else {
				list ( $page_size, $page_index ) = explode ( ':', base64_decode ( $pagetoken ) );
			}
		} else {
			$page_size = 20;
			$page_index = 1;
		}
		$url .= "&page_size=$page_size&page_index=$page_index";

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
			$cache_key = $this->getKey ( $url );

			if (! ($rs = $this->cached ( $cache_key ))) {
				// echo "No Cache\n";
				$rs = $this->request ( $url );
				$rs ['body'] = json_decode ( $rs ['body'], 1 );
				if ($rs ['body'] ['status'] != '0')
					throw new \Exception ( $rs ['body'] ['message'] . ', Tencent URL:' . $url );
				$this->store ( $cache_key, $rs, $this->cachetime ); // 5分钟缓存

				$this->mapsRequest ( $this->engine, $pagefrom, $this->url_request ); // 统计
			}
		} catch ( \Exception $e ) {
			$return ['c'] = 1;
			$return ['msg'] = $e->getMessage ();

			trigger_error ( $return ['msg'], E_USER_ERROR );

			return $return;
		}

		foreach ( $rs ['body'] ['data'] as $row ) {
			if (empty ( $row ['id'] ))
				continue;
			$return ['data'] ['items'] [] = array (
					'location' => $row ['location'],
					'id' => $row ['id'],
					'name' => $row ['title'],
					'address' => isset ( $row ['address'] ) ? $row ['address'] : $row ['title']
			);
		}
		if ($rs ['body'] ['count'] > $page_size * $page_index) {
			$return ['data'] ['pagetoken'] = base64_encode ( "$page_size:" . ($page_index + 1) );
		}

		return $return;
	}

}
