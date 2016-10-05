<?php
namespace strong\web;

use yii;
use strong\xhprof\xhprofer;
use strong\helpers\VarDumperHelper;

/**
 * api父类控制器
 *
 * Class Controller
 * @package strong\web
 */
class ApiController extends \yii\web\Controller
{
    /**
     * 默认返回的HTTP CODE
     */
    const DEFAULT_HTTP_STATUS_CODE = 200;

    const CODE_OK           = 0;
    const CODE_PARAM        = 1;
    const CODE_OTHER        = 2;
    const CODE_403          = 403;

    public static $outCodeLabels = [
        self::CODE_OK => ['describe' => '成功', 'en_message' => '成功'],
        self::CODE_PARAM => ['describe' => '参数错误', 'en_message' => '参数错误'],
        self::CODE_OTHER => ['describe' => '其他错误', 'en_message' => '服务器繁忙, 请稍后再试'],
        self::CODE_403 => ['describe' => '没有权限', 'en_message' => '没有权限']
    ];

    /**
     * 输出的错误码
     */
    protected $_outputCode = self::CODE_OK;

    protected $_outputData = [];

    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            return true;
        } else {
            //return false;
            $this->setOutput(self::CODE_403);
        }
    }

    public function afterAction($action, $result)
    {
        $data = static::formatOutput($this->_outputCode, $this->_outputData);
        if (YII_DEBUG && (YII_ENV_DEV || YII_ENV_LOCAL) && ENABLE_VAR_DUMPER_ECHO) {
            $content = '<pre>' . VarDumperHelper::dumpAsString($data, 1000, true) . '</pre>';
            Yii::$app->Response->data = $this->renderContent($content);
        } else {
            static::outputJson($data);
        }

        static::setStatusCode(static::DEFAULT_HTTP_STATUS_CODE);

        return parent::afterAction($action, $result);
    }

    public function setOutput($code, $data = [])
    {
        $this->_outputCode = $code;
        $this->_outputData = $data;
    }

    public static function setStatusCode($code = null)
    {
        $code = null === $code ? static::DEFAULT_HTTP_STATUS_CODE : $code;
        Yii::$app->Response->setStatusCode($code);
    }

    public static function outputJson($data)
    {
        Yii::$app->Response->format = Response::FORMAT_RAW;
        Yii::$app->Response->getHeaders()->set('Content-Type', 'application/json; charset=UTF-8');
        Yii::$app->Response->data = JsonHelper::encode($data);
    }

    public static function formatOutput($code, $data = [])
    {
        $code = null === $code ? static::CODE_OK : $code;
        $code = isset(static::$outCodeLabels[$code]) ? $code : static::CODE_OTHER;

        $outdata = [
            'c' => $code,
            'msg' => static::$outCodeLabels[$code]['describe'],
            'data' => $data
        ];

        return $outdata;
    }
}
