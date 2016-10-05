<?php

namespace strong\maps;

use Yii;

class GoogleMap extends Maps
{
	#protected $PROXY = '192.168.119.70:8580'; # 内网测试时需要加上代理，否则无法请求Google API
	protected $URI = 'https://maps.googleapis.com/maps/api/';
	private $pagesize = 20;
	private $cachetime = 300; // 5分钟，如果不设置将不缓存（缓存相同URL的请求）

	public $engine ='google';
	protected  $maps =[];

	public function init()
	{
		parent::init();
	}

	public function __construct(){
		$this->maps=Yii::$app->config->get('maps');
		$this->KEY = $this->maps['maps_all_static']['maps']['google'];
	}
	public function getIp(){
		$ip_arr=$this->maps['maps_all_dynamic']['maps_connect'];
		return array($ip_arr['google_domain'],$ip_arr['google_ip']);
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
		$request_nums = 1; // 需要请求几次Google API
		$next_page_token = ''; // Google API的下页Token
		$pagefrom = $page;

		// 计算需要执行多少次查询
		$pagenum = 1; // 默认为当前页
		$pagesize = 20; // 默认为每页20条
		$google_pagenum = 1; // 默认第1页

		if ($pagetoken) {
			if (is_numeric ( $pagetoken )) {
				$pagesize = $pagetoken;
				$pagenum = 1;
				$request_nums = ceil ( $pagesize / $this->pagesize ); // 需要请求多少次
				$google_pagenum = $request_nums; // Google API处于第几页
			} else { // 多页
				list ( $pagenum, $pagesize, $google_pagenum, $next_page_token ) = explode ( ':', base64_decode ( $pagetoken ) );
				if ($pagesize * $pagenum > $google_pagenum * $this->pagesize) { // 要拉第多次
					$request_nums = ceil ( ($pagesize * $pagenum) / ($google_pagenum * $this->pagesize) );
					$google_pagenum = $request_nums;
				}
			}
		}

		// echo " $request_nums , $next_page_token \n";
		// echo $pagenum . '*' . $pagesize . ' = '.($pagenum * $pagesize) . ' '.$google_pagenum.'*'.$this->pagesize.'='.($google_pagenum * $this->pagesize);

		$types = $this->maps ['maps_all_static'] ['client_location_types'];

		$url = $this->URI . 'place/nearbysearch/json?key=' . $this->KEY . '&location=' . $location [0] ['lat'] . ',' . $location [0] ['lng'] . '&radius=' . $radius . '&language=' . $this->LANG;
		$url .= ($types [$location [1]] == 'gps') ? '&sensor=true' : '&sensor=false';
		if ($keyword)
			$url .= '&keyword=' . urlencode ( $keyword );
		if ($next_page_token)
			$next_page_token = '&pagetoken=' . $next_page_token;

			// 用于返回的结果
		$return = array (
				'c' => 0,
				'msg' => 'ok',
				'data' => array (
						'items' => array ()
				)
		);

		// 查询并缓结果
		$result = array (); // 保存所有的结果
		try {
			while ( $request_nums -- > 0 ) {
				$full_url = $url . ($next_page_token ?  : '');
				$cache_key = $this->getKey ( $full_url );

				// echo "$cache_key \n";
				if (! ($rs = $this->cached ( $cache_key ))) {
					$rs = $this->request ( $full_url );
					$rs ['body'] = json_decode ( $rs ['body'], 1 );
					if ($rs ['body'] ['status'] != 'OK')
						throw new \Exception ( $rs ['body'] ['status'] . ', Google URL:' . $full_url );
					$this->store ( $cache_key, $rs, $this->cachetime );

					$this->mapsRequest ( $this->engine, $pagefrom, $this->url_request ); // 统计
				}
				// error_log(print_r($rs,1), 3, '/tmp/vanni.log');
				// print_r($rs);

				$result = array_merge ( $result, $rs ['body'] ['results'] );
				if (isset ( $rs ['body'] ['next_page_token'] ))
					$next_page_token = '&pagetoken=' . $rs ['body'] ['next_page_token'];
			}
		} catch ( \Exception $e ) {
			$return ['c'] = 1;
			$return ['msg'] = $e->getMessage ();

			trigger_error ( $return ['msg'], E_USER_ERROR );

			return $return;
		}

		// 在结果中取出数据
		if ($next_page_token) {
			$return ['data'] ['pagetoken'] = base64_encode ( ($pagenum + 1) . ":$pagesize:$google_pagenum:" . substr ( $next_page_token, strlen ( '&pagetoken=' ) ) );
		}
		// 直接分片
		$total = count ( $result );
		for($i = ($pagenum - 1) * $pagesize, $j = 0; $i < $total && $j < $pagesize; $i ++, $j ++) { // i 开始下标， j 停止下标
			$row = $result [$i];
			$return ['data'] ['items'] [] = array (
					'location' => $row ['geometry'] ['location'],
					'id' => $row ['id'],
					'name' => $row ['name'],
					'address' => isset ( $row ['vicinity'] ) ? $row ['vicinity'] : $row ['name']
			);
		}
		# 先得到全部，再分片
		# foreach($result as $row){
		# $return['data']['items'][] = array(
		# 'location' => $row['geometry']['location'],
		# 'id'=>$row['id'],
		# 'name'=>$row['name'],
		# 'address'=> isset($row['vicinity']) ? $row['vicinity'] : $row['name']
		# );
		# }
		# $return['data']['items'] = array_slice($return['data']['items'], ($pagenum-1)*$pagesize , $pagesize);
		# print_r($return);

		return $return;
	}
}
