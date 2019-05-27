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

    public function getOthersInfo($openid)
    {
        $hide = DB::table('users')->where('openid',$openid)->pluck('hide_figure');
//        return $hide[0];
        if ($hide[0] == 1)         //隐藏
        {
            $detail = DB::table('users')->where('openid',$openid)->select('avatar_url','nickname','openid','signature','liked')->first();
        }
        else                    //显示
        {
            $detail = DB::table('users')->where('openid',$openid)->select('avatar_url','nickname','openid','weight','height','signature','liked')->first();
        }

        return $detail;
    }

    public function createFollow($from,$to)
    {
        if ($from == $to)
        {
            return -1;          //关注自己
        }
        else if ($this->checkIfFollowed($from,$to))
        {
            DB::table('follows')->insert([
                'from'  =>  $from,
                'to'    =>  $to,
                'created_at'    =>  Carbon::now(),
                'updated_at'    =>  Carbon::now()
            ]);
            return 1;           //成功
        }
        else
        {
            return -2;          //已经关注
        }

    }

    public function deleteFollow($from,$to)
    {
        DB::table('follows')->where([
            'from'  =>  $from,
            'to'    =>  $to
        ])->delete();
    }

    public function checkIfFollowed($from,$to)
    {
        $data = DB::table('follows')->where([
            'from'  =>  $from,
            'to'    =>  $to
        ])->first();
        return ($data == null);   //没关注返回1
    }

    public function getAllFollowed($openid)
    {
        $data = DB::table('follows')->where([
            'from'  =>  $openid
        ])  ->select('to')
            ->orderBy('created_at', 'desc')
            ->paginate(30);
        return $data;
    }

    public function getNicknameByOpenid($openid)
    {
        $data = DB::table('users')->where('openid',$openid)->pluck('nickname');
        return $data[0];
    }

//    public function getIdByOpenid($openid)
//    {
//        $id = DB::table('users')->where('openid',$openid)->pluck('id');
//        return $id;
//    }
//
//    public function getOpenidById($id)
//    {
//        $openid = DB::table('users')->where('id',$id)->pluck('openid');
//        return $openid;
//    }

}