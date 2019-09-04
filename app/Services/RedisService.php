<?php
/**
 * Created by PhpStorm.
 * User: yuse
 * Date: 19/5/17
 * Time: 下午6:23
 */
namespace App\Services;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;
Class RedisService
{
    private static $PREFIX_1 = 'fit:phone:';

    public static function getCache($key)
    {
        return Redis::get($key);
    }

    public static function getPrefix_1()
    {
        return self::$PREFIX_1;
    }

    public static function checkCache($key)
    {
        return Redis::exists($key) == 1;
    }

    public static function setAccessToken($accessToken)        //微信相关
    {
        Redis::setex('fit:accessToken',6600,$accessToken);      //  110min * 60s
    }

    public static function setPhone($phone,$vCode,$limitTime = 1800)    // 30分钟内同一个验证码
    {
        $time = Carbon::now()->timestamp;
        $value = json_encode([
            'vCode' =>  $vCode,
            'time'  =>  $time
        ]);
        Redis::setex(self::$PREFIX_1.$phone, $limitTime, $value);
    }

    public static function checkPhoneFreq($phone)   // 0为 ok, 1为太快
    {
        if (!self::checkCache(self::$PREFIX_1.$phone)) {
            return false;
        }
        $data = self::getCache(self::$PREFIX_1.$phone);
        $data = json_decode($data);
        if(Carbon::now()->timestamp - $data->time < 60) //60s内视为太快
        {
            return true;
        }
        return false;
    }


}