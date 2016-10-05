<?php

namespace strong\helpers;

final class TimeHelper
{
    /**
	 * 函数getTheMsec获取当前的微秒时间戳;
	 *
	 * @param void;
	 *
	 * @return float;
	 *
	 */
    public static function getSec()
    {
        list($msec, $sec) = explode(' ', microtime());
        return ($msec + $sec) * 1000000;
    }

    //'Y-d-m H:i:s u'
    public static function date($format, $time = null)
    {
        if(null === $time){
            list($msec, $sec) = explode(' ', microtime());
        }else{
            list($sec, $msec) = explode('.', $time);
            $msec = floatval("0.{$msec}");
        }
        return date(strtr($format, ['u' => str_pad(round($msec * 1000), 3, '0', STR_PAD_LEFT)]), $sec);
    }

    /*
    * 生日转化成年龄
    *
    * @param $birthday
    * @return mixed
    */
    /**
    * 生日转化成年龄
    *
    * @param $birthday     出生日期
    * @param int $type     转化方式：默认0，简单计算(年-年)；1，精确计算
    * @return mixed
    */
    public static function getAge($birthday, $type = 0)
    {
        $birth = date('Y-m-d', $birthday);
        $now   = date('Y-m-d', time());
        list($birthYear, $birthMonth, $birthDay) = explode('-', $birth);
        list($currentYear, $currentMonth, $currentDay) = explode('-', $now);

        if ($type == 0) {
            //{{粗略算法
            return $currentYear - $birthYear;
        } else {
            //{{精确算法
            $age = $currentYear - $birthYear - 1;
            if ($currentMonth > $birthMonth || ($currentMonth == $birthMonth && $currentDay >= $birthDay)) {
                $age++;
            }
            return $age;
            //}}
        }
    }

    /**
    *
    * 随即生成指定年龄的生日
    *
    * @param $age
    * @param int $type     转化方式：默认0，简单计算(年-年)；1，精确计算
    * @return int
    */
    public static function randBirthday($age, $type = 0)
    {
        list($currentYear, $currentMonth, $currentDay) = explode('-', date('Y-m-d', time()));
        $tmp_year = $currentYear - $age;

        //随机一个日期
        list($rYear, $rMonth, $rDay) = explode('-', date('Y-m-d', rand(0, time())));

        if ($type != 0) {
            if ($rMonth > $currentMonth || ($currentMonth == $rMonth && $rDay > $currentDay)) {
                $tmp_year--;
            }
        }
        $birthday = strtotime($tmp_year . '-' . $rMonth . '-' . $rDay);
        return $birthday;
    }

    /**
	 * 函数getXingZuoByTimestamp,根据时间戳得到星座;
	 *
	 * @param timestamp int [必选]	时间戳;
	 *
	 * @return string;
	 *
	 */
    private static $_xingZuo = [
        1 => [20 => '宝瓶座'],
        2 => [19 => '双鱼座'],
        3 => [21 => '白羊座'],
        4 => [20 => '金牛座'],
        5 => [21 => '双子座'],
        6 => [22 => '巨蟹座'],
        7 => [23 => '狮子座'],
        8 => [23 => '处女座'],
        9 => [23 => '天秤座'],
        10 => [24 => '天蝎座'],
        11 => [22 => '射手座'],
        12 => [22 => '摩羯座'],
    ];
    public function getXingZuo($timestamp)
    {
        $month = idate('n', $timestamp);
        $day = idate('j', $timestamp);
        list($startDay, $xingZuoName) = each(static::$_xingZuo[$month]);
        if($day < $startDay){
            list($startDay, $xingZuoName) = each(static::$_xingZuo[($month - 1 < 0) ? $month = 11 : $month -= 1]);
        }
        return $xingZuoName;
    }

    /**
	 * 函数getPeriodOfYear,根据一个时间戳得到当年的起始时间戳和结束时间戳;
	 *
	 * @param timestamp int [必须] 时间戳;
	 *
	 * @return array,键@start:开始时间戳,键@end:结束时间戳;
	 */
    public static function getPeriodOfYear($timestamp)
    {
        $year = date('Y', $timestamp);
        return [
            'start' => strtotime($year . '-01-01 00:00:00'),
            'end' => strtotime($year . '-12-31 23:59:59')
        ];
    }

    /**
	 * 函数getPeriodOfMonth,根据一个时间戳得到当月的起始时间戳和结束时间戳;
	 *
	 * @param timestamp int [必须] 时间戳;
	 *
	 * @return array,键@start:开始时间戳,键@end:结束时间戳;
	 */
    public static function getPeriodOfMonth($timestamp)
    {
        return [
            'start' => strtotime(date('Y-m-01 00:00:00', $timestamp)),
            'end' => strtotime(date('Y-m-t 23:59:59', $timestamp))
        ];
    }

    /**
	 * 函数getPeriodOfWeek,根据一个时间戳得到当周的起始时间戳和结束时间戳;
     * 0（表示星期天）到 6（表示星期六）
	 *
	 * @param timestamp int [必须] 时间戳;
	 *
	 * @return array,键@start:开始时间戳,键@end:结束时间戳;
	 */
    public static function getPeriodOfWeek($timestamp, $weekStart = 0)
    {
        $dateInfoArray = getdate($timestamp);

        $result = array();
        $result['start'] = $timestamp - $dateInfoArray['wday'] * 86400;
        $result['start'] = $result['start'] + ($weekStart * 86400);
        $result['start'] = strtotime(date('Y-m-d 00:00:00', $result['start']));

        $result['end'] = $result['start'] + 604799;
        return $result;
    }

    /**
	 * 函数getPeriodOfDay,根据一个时间戳得到当天的起始时间戳和结束时间戳;
	 *
	 * @param timestamp int [必须] 时间戳;
	 *
	 * @return array,键@start:开始时间戳,键@end:结束时间戳;
	 */
    public static function getPeriodOfDay($timestamp)
    {
        $dateString = date('Y-m-d', $timestamp);
        return [
            'start' => strtotime($dateString . ' 00:00:00'),
            'end' => strtotime($dateString . ' 23:59:59')
        ];
    }

    /**
	 * 函数getPeriodOfQuarter,根据一个时间戳得到当季度的起始时间戳和结束时间戳;
	 *
	 * @param timestamp int [必须] 时间戳;
	 *
	 * @return array,键@start:开始时间戳,键@end:结束时间戳;
	 */
    public static function getPeriodOfQuarter($timestamp)
    {
        $base = [
            1 => [1, 3],
            2 => [4, 6],
            3 => [7, 9],
            4 => [10, 12]
        ];

        $dateInfoArray = getdate($timestamp);
        $quarter = ceil($dateInfoArray['mon'] / 3);
        return array(
            'start' => strtotime($dateInfoArray['year'] . '-' . $base[$quarter][0] . '-01 00:00:00'),
            'end' => strtotime(date('Y-m-t 23:59:59', strtotime($dateInfoArray['year'] . '-' . $base[$quarter][1] . '-01 00:00:00')))
        );
    }

    public static function get($timestamp){
 		$year = date('Y', $timestamp);
 		$month = date('n', $timestamp);

 		$q = $r = 0;

 		$poor = $year - 1977;
 		while (true) {
 			for($r = 0; $r < 4; $r++){
 				if($poor == 4 * $q + $r){break 2;}
 			}
 			$q++;
 		}

 		//阴历日期=14q+10.6(r+1)+年内日期序数-29.5n
 		$emporary = (14 * $q) + (10.6 * ($r + 1)) + date('z', $timestamp) + 1;
 		var_dump(ceil($emporary - (intval($emporary / 29.5) * 29.5)));
    }

    /**
     * 计算当前时间和格林威治时间相差多少秒
     * @return [type] [description]
     */
    public static function getJetLag(){
        $time = time();
        return time() - strtotime(gmdate('y-m-d H:i:s', time()));
    }
}
