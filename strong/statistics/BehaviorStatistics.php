<?php
namespace strong\statistics;

/**
 * Class 行为统计服务类
 * @auhtor  neil.liang
 * @package strong\statistics
 *
 * \Yii::$app->behaviorStatistics->put(PutStat::TYPE_VISIT, PutStat::STYPE_DEFAULE, 123, 456, '', 1);
 *
 */
class BehaviorStatistics extends \yii\base\Object
{
    public $ip;
    public $port;

    //列外用户uid，不做统计
    public $exceptionUids = [];//698900025,2620999

    //{{
    const STYPE_DEFAULE                         = 1;

    const TYPE_MSG                              = 1;    //聊天文字消息
    const STYPE_MSG_TEXT                        = self::STYPE_DEFAULE;
    const STYPE_MSG_VOICE                       = 2;
    const STYPE_MSG_IMG                         = 3;

    const TYPE_SYSTEM_MSG                       = 2;    //系统文字消息

    const TYPE_RADAR                            = 3;    //雷达刷新

    const TYPE_MEET                             = 4;    //全部邂逅
    const STYPE_MEET_ALL                        = self::STYPE_DEFAULE;   //邂逅
    const STYPE_MEET_NEW                        = 2;                     //新人邂逅

    const TYPE_FAVORITE                         = 5;    //邂逅
    const STYPE_FAVORITE_YES                    = self::STYPE_DEFAULE;    //心动
    const STYPE_FAVORITE_NO                     = 2;                      //不心动

    const TYPE_HOME_FAVORITE                    = 6;   //个人页心动

    const TYPE_VISIT                            = 7;    //查看资料页
    const STYPE_VISIT_OTHER                     = self::STYPE_DEFAULE;    //查看他人资料页
    const STYPE_VISIT_SELF                      = 2;                      //查看个人资料页

    const TYPE_FRIEND                           = 8;    //加好友
    const STYPE_FRIEND_SEND                     = self::STYPE_DEFAULE;    //加好友
    const STYPE_FRIEND_ACCEPT                   = 2;                      //同意加好友

    const TYPE_PHOTO                            = 9;    //相册
    const STYPE_PHOTO_UPLOAD                    = self::STYPE_DEFAULE;    //上传照片
    const STYPE_PHOTO_DEL                       = 2;   //删除照片

    const TYPE_BLACK                            = 10;   //拉黑

    const TYPE_REPORT                           = 11;   //举报
    const STYPE_REPORT_DEFAULE                  = self::STYPE_DEFAULE;   //举报漂流瓶
    const STYPE_REPORT_DRIFTBOTTLE              = 2;   //举报漂流瓶

    const TYPE_LOGIN                            = 12;   //登陆
    const STYPE_LOGIN_RADAR                     = self::STYPE_DEFAULE;   //雷达登陆（密码登陆）
    const STYPE_LOGIN_SOCIAL                    = 2;                     //联合登陆
    const STYPE_LOGIN_MESSAGE                   = 3;                     //雷达短信登陆

    const TYPE_REGISTER                         = 13;   //注册
    const STYPE_REGISTER_RADAR                  = self::STYPE_DEFAULE;   //雷达注册
    const STYPE_REGISTER_SOCIAL                 = 2;                     //联合注册

    const TYPE_USERINFO                         = 14;   //用户资料
    const STYPE_USERINFO_UPDATE                 = self::STYPE_DEFAULE;   //修改信息
    const STYPE_USERINFO_FEELING                = 2;                     //修改心情
    const STYPE_USERINFO_AVATAR                 = 3;                     //修改头像

    const TYPE_DRIFTBOTTLE_CREATE               = 10001;                    //创建漂流瓶
    const STYPE_DRIFTBOTTLE_CREATE_TEXT         = self::STYPE_DEFAULE;   //创建文本漂流瓶
    const STYPE_DRIFTBOTTLE_CREATE_VOICE        = 2;                     //创建语音漂流瓶
    const STYPE_DRIFTBOTTLE_CREATE_IMG          = 3;                     //创建图片漂流瓶

    const TYPE_DRIFTBOTTLE_REPLY                = 10002;                    //回复漂流瓶
    const STYPE_DRIFTBOTTLE_REPLY_TEXT          = self::STYPE_DEFAULE;   //回复文本漂流瓶
    const STYPE_DRIFTBOTTLE_REPLY_VOICE         = 2;                     //回复语音漂流瓶
    const STYPE_DRIFTBOTTLE_REPLY_IMG           = 3;                     //回复图片漂流瓶

    const TYPE_DRIFTBOTTLE_MESSAGE              = 10003;                    //漂流瓶消息
    const STYPE_DRIFTBOTTLE_MESSAGE_TEXT        = self::STYPE_DEFAULE;   //文本漂流瓶
    const STYPE_DRIFTBOTTLE_MESSAGE_VOICE       = 2;                     //语音漂流瓶
    const STYPE_DRIFTBOTTLE_MESSAGE_IMG         = 3;                     //图片漂流瓶

    const TYPE_DRIFTBOTTLE                      = 10004;   //漂流瓶
    const STYPE_DRIFTBOTTLE_OPEN                = self::STYPE_DEFAULE;   //捡漂流瓶
    const STYPE_DRIFTBOTTLE_THROW               = 2;                     //扔回漂流瓶
    const STYPE_DRIFTBOTTLE_DELETE              = 3;                     //删除漂流瓶（漂流瓶创建者删除）
    const STYPE_DRIFTBOTTLE_CLOSE               = 4;                     //关闭漂流瓶功能（用户熊系统设置）
    const STYPE_DRIFTBOTTLE_REPORT              = 5;                     //举报漂流瓶（举报接口里）

    const TYPE_DYNAMIC                          = 20001;                 //动态圈
    const STYPE_DYNAMIC_CREATE                  = self::STYPE_DEFAULE;   //发表动态
    const STYPE_DYNAMIC_DELETE                  = 2;                     //删除一个动态
    const STYPE_DYNAMIC_GETONE                  = 3;                     //获取单个动态
    const STYPE_DYNAMIC_COMMENTS                = 4;                     //获取评论
    const STYPE_DYNAMIC_COMMENTS_CREATE         = 5;                     //评论用户的动态
    const STYPE_DYNAMIC_COMMENTS_DELETE         = 6;                     //删除评论
    const STYPE_DYNAMIC_GETLIST                 = 7;                     //获取自己动态
    const STYPE_DYNAMIC_OTHER_GETLIST           = 8;                     //获取他人动态列表
    const STYPE_DYNAMIC_LIKE                    = 9;                     //点赞
    const STYPE_DYNAMIC_LIKE_DELETE             = 10;                     //取消点赞
    const STYPE_DYNAMIC_LAST                    = 11;                    //获取用户最后的动态(自己)
    const STYPE_DYNAMIC_OTHER_LAST              = 12;                    //获取用户最后的动态（他人）
    const STYPE_DYNAMIC_LIKE_USER               = 13;                    //获取为该动态点赞的用户
    const STYPE_DYNAMIC_FRIENDS                 = 14;                    //朋友的动态
    const STYPE_DYNAMIC_LBS                     = 15;                    //附近的动态
    const STYPE_DYNAMIC_TAG_LIST                = 16;                    //动态标签列表
    const STYPE_DYNAMIC_TAG_SEARCH              = 17;                    //搜索动态标签
    const STYPE_DYNAMIC_TAG_FEED                = 18;                    //标签下的动态
    const STYPE_DYNAMIC_CITY_FEED               = 19;                    //城市下的动态

    const TYPE_MAPS            					= 30001;                 //地图
    const STYPE_MAPS_TENCENT            	    = self::STYPE_DEFAULE;   //腾讯地图
    const STYPE_MAPS_BAIDU             			= 2;                     //百度地图
    const STYPE_MAPS_GOOGLE            			= 3;                 	 //Google地图

    //superpowers_advice
    const TYPE_SUPERPOWERS                      = 60001;      	            //超能力
    const TYPE_SUPERPOWERS_LIST                 = self::STYPE_DEFAULE;      //获取列表
    const TYPE_SUPERPOWERS_ADVICE               = 2;                        //建议添加

    //}}

    /**
     * @var array
     */
    public $_statType = array();

    public $fieldsOrder = array(
        'type', 'sub_type', 'uid', 'tid', 'msg', 'count', 'server_ip', 'created'
    );

    public function __construct($ip, $port)
    {
        $this->ip = $ip;
        $this->port = $port;

        $this->_statType = array(
            //消息
            self::TYPE_MSG.'_'.self::STYPE_MSG_TEXT  => '聊天文字消息',
            self::TYPE_MSG.'_'.self::STYPE_MSG_VOICE  => '聊天语音消息',
            self::TYPE_MSG.'_'.self::STYPE_MSG_IMG  => '聊天图片消息',

            //系统消息
            self::TYPE_SYSTEM_MSG.'_'.self::STYPE_DEFAULE => '系统文字消息',

            //雷达刷新
            self::TYPE_RADAR.'_'.self::STYPE_DEFAULE => '雷达刷新',

            //邂逅
            self::TYPE_MEET.'_'.self::STYPE_DEFAULE => '邂逅',

            //心动
            self::TYPE_FAVORITE.'_'.self::STYPE_FAVORITE_YES => '邂逅心动',
            self::TYPE_FAVORITE.'_'.self::STYPE_FAVORITE_NO => '邂逅不心动',

            //个人页心动
            self::TYPE_HOME_FAVORITE.'_'.self::STYPE_DEFAULE => '个人页心动',

            //查看资料
            self::TYPE_VISIT.'_'.self::STYPE_VISIT_OTHER => '查看他人资料',
            self::TYPE_VISIT.'_'.self::STYPE_VISIT_SELF => '查看自己资料',

            //好友
            self::TYPE_FRIEND.'_'.self::STYPE_FRIEND_SEND => '申请加好友',
            self::TYPE_FRIEND.'_'.self::STYPE_FRIEND_ACCEPT => '同意加好友',

            //相册
            self::TYPE_PHOTO.'_'.self::STYPE_PHOTO_UPLOAD => '上传照片',
            self::TYPE_PHOTO.'_'.self::STYPE_PHOTO_DEL => '删除照片',

            //拉黑
            self::TYPE_BLACK.'_'.self::STYPE_DEFAULE => '拉黑',

            //举报
            self::TYPE_REPORT.'_'.self::STYPE_REPORT_DEFAULE => '举报',
            self::TYPE_REPORT.'_'.self::STYPE_REPORT_DRIFTBOTTLE => '举报漂流瓶',

            //登陆
            self::TYPE_LOGIN.'_'.self::STYPE_LOGIN_RADAR => '雷达密码登陆',
            self::TYPE_LOGIN.'_'.self::STYPE_LOGIN_SOCIAL => 'facebook登陆',
            self::TYPE_LOGIN.'_'.self::STYPE_LOGIN_MESSAGE => '雷达短信登陆',

            //注册
            self::TYPE_REGISTER.'_'.self::STYPE_REGISTER_RADAR => '雷达注册',
            self::TYPE_REGISTER.'_'.self::STYPE_REGISTER_SOCIAL => 'facebook注册',

            //用户资料
            self::TYPE_USERINFO.'_'.self::STYPE_USERINFO_UPDATE => '修改个人信息',
            self::TYPE_USERINFO.'_'.self::STYPE_USERINFO_FEELING => '修改心情',
            self::TYPE_USERINFO.'_'.self::STYPE_USERINFO_AVATAR => '修改头像',

            //{{漂流瓶
            self::TYPE_DRIFTBOTTLE_CREATE.'_'.self::STYPE_DRIFTBOTTLE_CREATE_TEXT => '创建文本漂流瓶',
            self::TYPE_DRIFTBOTTLE_CREATE.'_'.self::STYPE_DRIFTBOTTLE_CREATE_VOICE => '创建语音漂流瓶',
            self::TYPE_DRIFTBOTTLE_CREATE.'_'.self::STYPE_DRIFTBOTTLE_CREATE_IMG => '创建图片漂流瓶',

            self::TYPE_DRIFTBOTTLE_REPLY.'_'.self::STYPE_DRIFTBOTTLE_REPLY_TEXT => '回复文本漂流瓶',
            self::TYPE_DRIFTBOTTLE_REPLY.'_'.self::STYPE_DRIFTBOTTLE_REPLY_VOICE => '回复语音漂流瓶',
            self::TYPE_DRIFTBOTTLE_REPLY.'_'.self::STYPE_DRIFTBOTTLE_REPLY_IMG => '回复图片漂流瓶',

            self::TYPE_DRIFTBOTTLE_MESSAGE.'_'.self::STYPE_DRIFTBOTTLE_MESSAGE_TEXT => '文本消息',
            self::TYPE_DRIFTBOTTLE_MESSAGE.'_'.self::STYPE_DRIFTBOTTLE_MESSAGE_VOICE => '语音消息',
            self::TYPE_DRIFTBOTTLE_MESSAGE.'_'.self::STYPE_DRIFTBOTTLE_MESSAGE_IMG => '图片消息',

            self::TYPE_DRIFTBOTTLE.'_'.self::STYPE_DRIFTBOTTLE_OPEN => '捡漂流瓶',
            self::TYPE_DRIFTBOTTLE.'_'.self::STYPE_DRIFTBOTTLE_THROW => '扔回漂流瓶',
            self::TYPE_DRIFTBOTTLE.'_'.self::STYPE_DRIFTBOTTLE_DELETE => '删除漂流瓶',
            self::TYPE_DRIFTBOTTLE.'_'.self::STYPE_DRIFTBOTTLE_CLOSE => '关闭漂流瓶功能',
            self::TYPE_DRIFTBOTTLE.'_'.self::STYPE_DRIFTBOTTLE_REPORT => '举报漂流瓶',
            //}}

            //动态圈
            self::TYPE_DYNAMIC.'_'.self::STYPE_DYNAMIC_CREATE => '发表动态',
            self::TYPE_DYNAMIC.'_'.self::STYPE_DYNAMIC_DELETE => '删除动态',
            self::TYPE_DYNAMIC.'_'.self::STYPE_DYNAMIC_GETONE => '获取单个动态详情',
            self::TYPE_DYNAMIC.'_'.self::STYPE_DYNAMIC_COMMENTS => '获取评论',
            self::TYPE_DYNAMIC.'_'.self::STYPE_DYNAMIC_COMMENTS_CREATE => '发表评论',
            self::TYPE_DYNAMIC.'_'.self::STYPE_DYNAMIC_COMMENTS_DELETE => '删除评论',
            self::TYPE_DYNAMIC.'_'.self::STYPE_DYNAMIC_GETLIST => '获取自己动态列表',
            self::TYPE_DYNAMIC.'_'.self::STYPE_DYNAMIC_OTHER_GETLIST => '获取他人动态列表',
            self::TYPE_DYNAMIC.'_'.self::STYPE_DYNAMIC_LIKE => '动态点赞',
            self::TYPE_DYNAMIC.'_'.self::STYPE_DYNAMIC_LIKE_DELETE => '取消点赞',
            self::TYPE_DYNAMIC.'_'.self::STYPE_DYNAMIC_LAST => '获取自己最后动态',
            self::TYPE_DYNAMIC.'_'.self::STYPE_DYNAMIC_OTHER_LAST => '获取他人最后动态',
            self::TYPE_DYNAMIC.'_'.self::STYPE_DYNAMIC_LIKE_USER => '点赞的用户',
            self::TYPE_DYNAMIC.'_'.self::STYPE_DYNAMIC_FRIENDS => '朋友的动态',
            self::TYPE_DYNAMIC.'_'.self::STYPE_DYNAMIC_LBS => '附近的动态',
            self::TYPE_DYNAMIC.'_'.self::STYPE_DYNAMIC_TAG_LIST => '动态标签列表',
            self::TYPE_DYNAMIC.'_'.self::STYPE_DYNAMIC_TAG_SEARCH => '搜索动态标签',
            self::TYPE_DYNAMIC.'_'.self::STYPE_DYNAMIC_TAG_FEED => '标签下的动态',
            self::TYPE_DYNAMIC.'_'.self::STYPE_DYNAMIC_CITY_FEED => '城市下的动态',

            //地图
            self::TYPE_MAPS.'_'.self::STYPE_MAPS_TENCENT => '腾讯地图',
            self::TYPE_MAPS.'_'.self::STYPE_MAPS_BAIDU => '百度地图',
            self::TYPE_MAPS.'_'.self::STYPE_MAPS_GOOGLE => 'Google地图',

            //超能力
            self::TYPE_SUPERPOWERS.'_'.self::TYPE_SUPERPOWERS_LIST => '超能力列表',
            self::TYPE_SUPERPOWERS.'_'.self::TYPE_SUPERPOWERS_ADVICE => '建议添加超能力',
        );
    }

    public static $serverIp;

    public function GetServerIp()
    {
        if(null === static::$serverIp){
            static::$serverIp = getServerIp();
        }
        return static::$serverIp;
    }

    /**
     *
     * 去除监控消息
     * 正式
     * 698900025=>698900026
     * 测试
     * 2620999=>2621000
     *
     * @param $type
     * @param $sub_type
     * @param $uid              行为主动者
     * @param string $tid       行为被动者
     * @param string $msg       如果是消息，保存消息内容；非消息，''
     * @param int $count
     * @return bool
     */
    public function put($type, $sub_type, $uid, $tid = '', $msg = '', $count = 1)
    {
        //{{去除监控消息
        if($type == self::TYPE_MSG && in_array($uid,$this->exceptionUids)){
            return true;
        }
        //}}

        $created = time();
        $serverIp = $this->GetServerIp();
        $msg = strtr($msg, array(',' => '，', '"', "＂"));
        $msg = "{$type},{$sub_type},{$uid},{$tid},{$msg},{$count},{$serverIp},{$created}";

        if(false !== ($sock = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP))){
            @socket_sendto($sock, $msg, strlen($msg), 0, $this->ip, $this->port);
            @socket_close($sock);
            return true;
        }

        return false;
    }
}


