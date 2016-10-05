<?php

namespace strong\dfs;

/**
 * Class DFS服务类(文件存储系统)
 * @auhtor  neil.liang
 * @package strong\dfs
 *
 * $api = new DFSApi($url);
 *
 * TODO $file_id待定,涉及file_id都待定
 * $result = $api->fileInfo($file_id);
 *
 * $result = $api->uploadFile('/tmp/Lighthouse.jpg');
 * 返回示例：
 * [
 *      'ret' => 0
 *      'msg' => 'ok'
 *      'error_code' => 0
 *      'data' => [
 *          'source_id' => 0
 *          'create_timestamp' => 1429186578
 *          'file_size' => 8149
 *          'url' => 'http://dfs.idreamsky.com/group1/M00/2A/1C/CjICUFUvqBKAct85AAAf1ahfVPA650.png'
 *      ]
 * ]
 *
 * $result = $api->updateFile('/tmp/Desert.jpg', 'group1/M00/00/01/wKgCblBJXrbAhVbEAAvWFm7DIL0329.jpg');
 * $result = $api->deleteFile('/tmp/Koala.jpg', 0, 'group1/M00/00/01/wKgCblBHAKHR4hLmAAiQfHO6nNw369.jpg', '.200x200');
 * print_r($result);
 */
class DFS extends \yii\base\Object
{
    const RETURN_JSON = 'json';

    public $connecttimeout = 10;
    public $timeout = 10;
    public $ssl_verifypeer = false;

    public $useragent = '';

    public $http_info = array();

    public $debug = false;

    public $url = '';

    public $boundary = '';

    private $_http_code;
    private $_http_header;

    /**
     * 获取文件信息
     * @param $file_id string
     * @return array
     */
    public function fileInfo($file_id)
    {
        $params = array();
        $params['file_id'] = $file_id;
        $retval = $this->call('dfs/file_info', $params);
        return $retval;
    }

    /**
     * 上传文件
     * @param $file string
     * @param $ttl int
     * @param $file_id string
     * @param $prefix string
     * @return array
     */

    /**
     * 上传文件
     *
     * @param $file             上传文件
     * @param int $ttl
     * @param string $file_id
     * @param string $prefix
     * @param bool $cdn
     * @param string $callback_url
     * @param string $rename
     * @param string $mimetype
     * @param string $cdn_type
     * @param string $cdn_url
     * @param string $down_url
     * @return bool|mixed
     */
    public function uploadFile($file, $ttl = 0, $file_id = '', $prefix = '', $cdn = false, $callback_url = '',
                                $rename = '', $mimetype = '', $cdn_type = 'chinanetcenter', $cdn_url = '', $down_url = '')
    {
        if (!file_exists($file)) {
            return false;
        }
        $params = array();
        $params['file'] = '@' . $file;
        $params['ttl'] = $ttl;
        if (!empty($file_id)) {
            $params['file_id'] = $file_id;
        }
        if (!empty($prefix)) {
            $params['prefix'] = $prefix;
        }
        if (!empty($cdn)) {
            $params['cdn'] = $cdn;
        }
        if (!empty($callback_url)) {
            $params['callback_url'] = $callback_url;
        }
        $params['filename'] = !empty($rename) ? $rename : basename($file);
        $params['mimetype'] = !empty($mimetype) ? $mimetype : 'unknow';
        if (!empty($cdn_type)) {
            $params['cdn_type'] = $cdn_type;
        }
        if (!empty($cdn_url)) {
            $params['cdn_url'] = $cdn_url;
        }
        if (!empty($down_url)) {
            $params['down_url'] = $down_url;
        }
        $retval = $this->call('dfs/upload_file', $params, 'POST', true);

        return $retval;
    }

    /**
     * 修改文件
     * @param $filename string
     * @param $ttl int
     * @param $file_id string
     * @param $prefix string
     * @return array
     */
    public function updateFile($file, $file_id, $cdn = false, $callback_url = '', $rename = '', $mimetype = '', $cdn_type = 'chinanetcenter', $cdn_url = '', $down_url = '')
    {
        if (!file_exists($file)) {
            return false;
        }
        $params = array();
        $params['file'] = '@' . $file;
        $params['file_id'] = $file_id;
        if (!empty($cdn)) {
            $params['cdn'] = $cdn;
        }
        if (!empty($callback_url)) {
            $params['callback_url'] = $callback_url;
        }
        $params['filename'] = !empty($rename) ? $rename : basename($file);
        $params['mimetype'] = !empty($mimetype) ? $mimetype : 'unknow';
        if (!empty($cdn_type)) {
            $params['cdn_type'] = $cdn_type;
        }
        if (!empty($cdn_url)) {
            $params['cdn_url'] = $cdn_url;
        }
        if (!empty($down_url)) {
            $params['down_url'] = $down_url;
        }
        $retval = $this->call('dfs/update_file', $params, 'POST', true);
        return $retval;
    }

    /**
     * 删除文件
     * @param $file_id string
     * @return array
     */
    public function deleteFile($file_id)
    {
        $params = array();
        $params['file_id'] = $file_id;
        $retval = $this->call('dfs/delete_file', $params, 'POST');
        return $retval;
    }

    /**
     * 调用api
     * @param $command string
     * @param $params array
     * @param $method string
     * @param $multi boolean
     * @param $decode boolean
     * @param $format string
     * @return mixed
     */
    protected function call($command, $params = array(), $method = 'GET', $multi = false, $decode = true, $format = 'json')
    {
        $params['format'] = $format;
        foreach ($params as $key => $val) {
            if (strlen($val) == 0) {
                unset($params[$key]);
            }
        }
        $url = $this->url . '/' . $command;
        $response = $this->request($url, $method, $params, $multi);
        if ($decode) {
            if ($this->_http_code == 200 && $format == self::RETURN_JSON) {
                return json_decode($response, true);
            }
            $response = array('ret' => 2, 'error_code' => $this->_http_code, 'msg' => $response, 'data' => array());
        }
        return $response;
    }

    /**
     * 发送请求
     * @param $url string
     * @param $method string
     * @param $params array
     * @param $multi boolean
     * @param $extheaders array
     * @return string
     */
    protected function request($url, $method, $params, $multi = false, $extheaders = array())
    {
        return $this->http($url, $params, $method, $multi, $extheaders);
    }

    /**
     * http请求
     * @param $url string
     * @param $method string
     * @param $params array
     * @param $multi boolean
     * @param $extheaders array
     * @return string
     */
    protected function http($url, $params, $method = 'GET', $multi = false, $extheaders = array())
    {
        return $this->curl_http($url, $params, $method, $multi, $extheaders);
    }

    /**
     * curl方式
     * @param $url string
     * @param $method string
     * @param $params array
     * @param $multi boolean
     * @param $extheaders array
     * @return string
     */
    protected function curl_http($url, $params, $method = 'GET', $multi = false, $extheaders = array())
    {
        $ci = curl_init();
        curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
        curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);

        curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));

        curl_setopt($ci, CURLOPT_HEADER, false);

        $headers = (array)$extheaders;
        switch ($method) {
            case 'POST' :
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (!empty($params)) {
                    if ($multi) {
                        /**
                         * foreach ($multi as $key => $file) {
                         * $params[$key] = '@' . $file;
                         * }
                         * curl_setopt($ci, CURLOPT_POSTFIELDS, $params);
                         */
                        $headers[] = 'Expect: ';
                        curl_setopt($ci, CURLOPT_POSTFIELDS, $this->http_build_query_multi($params));
                        $headers[] = 'Content-Type: multipart/form-data; boundary=' . $this->boundary;
                    } else {
                        curl_setopt($ci, CURLOPT_POSTFIELDS, http_build_query($params));
                    }
                }
                break;
            case 'DELETE' :
            case 'GET' :
                $method == 'DELETE' && curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($params)) {
                    $url = $url . (strpos($url, '?') ? '&' : '?') . (is_array($params) ? http_build_query($params) : $params);
                }
                break;
        }
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE);
        curl_setopt($ci, CURLOPT_URL, $url);
        if ($headers) {
            curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($ci);
        $this->_http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
        $this->http_info = array_merge($this->http_info, curl_getinfo($ci));
        curl_close($ci);
        //print_r($this -> _http_code);
        //print_r($this -> _http_info);
        return $response;
    }

    /**
     * @param $ch obj
     * @param $header string
     * @return int
     */
    protected function getHeader($ch, $header)
    {
        $i = strpos($header, ':');
        if (!empty($i)) {
            $key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
            $value = trim(substr($header, $i + 2));
            $this->_http_header[$key] = $value;
        }
        return strlen($header);
    }

    /**
     * @param $params array
     * @return string
     */
    protected function http_build_query_multi($params)
    {
        if (!$params) {
            return '';
        }

        uksort($params, 'strcmp');

        $pairs = array();

        $this->boundary = $boundary = uniqid('------------------');
        $MPboundary = '--' . $boundary;
        $endMPboundary = $MPboundary . '--';
        $multipartbody = '';
        $filename = $params['filename'];
        $mimetype = $params['mimetype'];

        unset($params['filename']);
        unset($params['mimetype']);
        foreach ($params as $parameter => $value) {
            if (in_array($parameter, array('file')) && $value{0} == '@') {
                $url = ltrim($value, '@');
                $content = file_get_contents($url);
                //$array = explode('?', basename($url));
                $multipartbody .= $MPboundary . "\r\n";
                $multipartbody .= 'Content-Disposition: form-data; name="' . $parameter . '"; filename="' . $filename . '"' . "\r\n";
                if ($mimetype == 'unknow') {
                    $mimetype = $this->mime_content_type($url);
                }
                $multipartbody .= "Content-Type: " . $mimetype . "\r\n\r\n";
                $multipartbody .= $content . "\r\n";
            } else {
                $multipartbody .= $MPboundary . "\r\n";
                $multipartbody .= 'content-disposition: form-data; name="' . $parameter . "\"\r\n\r\n";
                $multipartbody .= $value . "\r\n";
            }

        }

        $multipartbody .= $endMPboundary . "\r\n";
        return $multipartbody;
    }

    private function mime_content_type($filename)
    {
        if (!is_file($filename)) {
            $error = 'Error: File not found';
            return $error;
        } elseif (function_exists('finfo_open')) {
            $finfo = @finfo_open(FILEINFO_MIME_TYPE);
            if (!$finfo) {
                $error = 'Error: Unable to verify MIME content type';
                return $error;
            }
            if ($mimetype = @finfo_file($finfo, $filename)) {
                finfo_close($finfo);
                return $mimetype;
            } else {
                $error = 'Error: Unable to verify MIME content type';
                return $error;
            }
        } elseif (function_exists('exec') && function_exists('escapeshellarg')) {
            if ($execmime = trim(@exec('file -bi ' . @escapeshellarg($filename)))) {
                return $execmime;
            } else {
                $error = 'Error: Unable to verify MIME content type';
                return $error;
            }
        } elseif (function_exists('pathinfo')) {
            if ($pathinfo = @pathinfo($filename)) {
                $mime_types = array('txt' => 'text/plain', 'htm' => 'text/html', 'html' => 'text/html', 'php' => 'text/html', 'css' => 'text/css', 'js' => 'application/javascript', 'json' => 'application/json', 'xml' => 'application/xml', 'swf' => 'application/x-shockwave-flash', 'flv' => 'video/x-flv',

                    // images
                    'png' => 'image/png', 'jpe' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'jpg' => 'image/jpeg', 'gif' => 'image/gif', 'bmp' => 'image/bmp', 'ico' => 'image/vnd.microsoft.icon', 'tiff' => 'image/tiff', 'tif' => 'image/tiff', 'svg' => 'image/svg+xml', 'svgz' => 'image/svg+xml',

                    // archives
                    'zip' => 'application/zip', 'rar' => 'application/x-rar-compressed', 'exe' => 'application/x-msdownload', 'msi' => 'application/x-msdownload', 'cab' => 'application/vnd.ms-cab-compressed',

                    // audio/video
                    'mp3' => 'audio/mpeg', 'qt' => 'video/quicktime', 'mov' => 'video/quicktime',

                    // adobe
                    'pdf' => 'application/pdf', 'psd' => 'image/vnd.adobe.photoshop', 'ai' => 'application/postscript', 'eps' => 'application/postscript', 'ps' => 'application/postscript',

                    // ms office
                    'doc' => 'application/msword', 'rtf' => 'application/rtf', 'xls' => 'application/vnd.ms-excel', 'ppt' => 'application/vnd.ms-powerpoint',

                    // open office
                    'odt' => 'application/vnd.oasis.opendocument.text', 'ods' => 'application/vnd.oasis.opendocument.spreadsheet',);
                $ext = $pathinfo['extension'];
                if (array_key_exists($ext, $mime_types)) {
                    return $mime_types[$ext];
                }
            } else {
                $error = 'Error: Unable to verify MIME content type';
                return $error;
            }
        } else {
            return 'application/octet-stream';
        }
    }

}


