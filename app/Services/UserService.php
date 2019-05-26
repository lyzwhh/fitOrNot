<?php
/**
 * Created by PhpStorm.
 * User: yuse
 * Date: 19/5/17
 * Time: 下午6:52
 */

namespace App\Services;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class UserService
{
    public function login()
    {

    }

    public function updateUser($userInfo)  //有则调用更新,没有就创建,因为会更新session_key
    {
        $user = DB::table('users')->where('openid',$userInfo['openid'])->first();
        if($user == null)
        {
            $userId = $this->createUser(['openid'   =>  $userInfo['openid'],
                                    'session_key'   =>  $userInfo['session_key']]);
        }
        else
        {
            DB::table('users')->where('openid',$userInfo['openid'])->update(['openid'   =>  $userInfo['openid'],
                                                                        'session_key'   =>  $userInfo['session_key']]);
            $userId = $user['id'];
        }
        return $userId;
    }

    public function createUser($userInfo)
    {
        $time = new Carbon();
        $userInfo = array_merge($userInfo,[
            'created_at' => $time,
            'updated_at' => $time
        ]);
        $userId = DB::table('users')->insertGetId($userInfo);
        return $userId;
    }
    public function setUserInfo($userInfo,$figure,$signature)
    {
        $data = array_merge($figure,[
            'signature' =>  $signature
        ]);
        DB::table('users')->where('openid',$userInfo->openid)->update($data);
    }
    public function getUserInfo($userInfo)
    {
        $detail = DB::table('users')->where('openid',$userInfo->openid)->select('phone', 'avatar_url','nickname','height','weight','signature','liked')->first();
        return $detail;
    }

    public function getOthersInfo($id)
    {
        $detail = DB::table('users')->where('id',$id)->select('phone', 'avatar_url','nickname','nickname','height','signature')->first();  //TODO ::修改,等原型需要展示什么
        return $detail;
    }

    public function getIdByOpenid($openid)
    {
        $id = DB::table('users')->where('openid',$openid)->pluck('id');
        return $id;
    }

    public function getOpenidById($id)
    {
        $openid = DB::table('users')->where('id',$id)->pluck('openid');
        return $openid;
    }
}