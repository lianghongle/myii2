<?php

namespace strong\helpers;

class GPSHelper
{
	/**
	 * 就算两个经纬度的距离
	 * @param  $location 经纬度, 格式: longitude,latitude
	 * @return foolt 单位米
	 */
    public static function getDistance($locationA, $locationB)
    {
        list($lngA, $latA) = explode(',', $locationA);
        list($lngB, $latB) = explode(',', $locationB);

        $earthRadius = 6367000;
        $latA = ($latA * pi() ) / 180;
        $lngA = ($lngA * pi() ) / 180;
        $latB = ($latB * pi() ) / 180;
        $lngB = ($lngB * pi() ) / 180;
        $calcLongitude = $lngB - $lngA;
        $calcLatitude = $latB - $latA;
        $stepOne = pow(sin($calcLatitude / 2), 2)
        		+ cos($latA) * cos($latB) * pow(sin($calcLongitude / 2), 2);
        $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
        $calculatedDistance = $earthRadius * $stepTwo;
        return $calculatedDistance;
    }
}
