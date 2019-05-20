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
    public function getCache($key)
    {
        return Redis::get($key);
    }

    public function checkCache($key)
    {
        return Redis::exists($key) == 1;
    }

    public function setAccessToken($accessToken)
    {
        Redis::setex('accessToken',6600,$accessToken);      //  110min * 60s
    }

}