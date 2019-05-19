<?php
/**
 * Created by PhpStorm.
 * User: yuse
 * Date: 2018/12/6
 * Time: 21:32
 */
namespace App\Services;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TokenService
{
    public static $EXPIRE_TIME = 720;    //30天

    public function createToken($userId)
    {
        $tokenStr = md5(uniqid());
        $time = new Carbon();
        $outTime = new Carbon();
        $outTime->addHour(self::$EXPIRE_TIME);  //小时
        $data = [
            'user_id' => $userId,
            'updated_at' => $time,
            'created_at' => $time,
            'expires_at' => $outTime,
            'token' => $tokenStr,
        ];
        DB::table('tokens')->insert($data);
        return $tokenStr;
    }

    public function updateToken($userId)
    {
        $time = new Carbon();
        $outTime = new Carbon();
        $outTime->addHour(self::$EXPIRE_TIME);
        $tokenStr = md5(uniqid());
        $data = [
            'token' => $tokenStr,
            'updated_at' => $time,
            'expires_at' => $outTime
        ];

        DB::table('tokens')->where('user_id', $userId)->update($data);
        return $tokenStr;
    }

    public function makeToken($userId)
    {
        $tokenStr = DB::table('tokens')->where('user_id', $userId)->first();
        if ($tokenStr == null)
        {
            $tokenStr = $this->createToken($userId);
        }
        else
        {
            $tokenStr = $this->updateToken($userId);
        }
        return $tokenStr;
    }

    public function verifyToken($tokenStr)
    {
        $res = $this->getToken($tokenStr);
        if($res == null)
            return -1;
        else{
            $time = new Carbon();
            if ($res->expires_at > $time) {
                return 1;
            } else {
                return 0;
            }
        }
    }
    public function getToken($tokenStr)
    {
        return DB::table('tokens')->where('token',$tokenStr)->first();
    }

    public function getUserByToken($tokenStr)
    {
        $tokenInfo = $this->getToken($tokenStr);
        $userInfo=DB::table('users')->where('id',$tokenInfo->user_id)->select('id','openid','session_key','phone',
                                                                        'avatar_url','nickname','figure','signature')->first();  //修改
        return $userInfo;
    }

}