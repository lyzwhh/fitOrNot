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

    public function updateUser($userInfo)  //  微信用    ——    有则调用更新,没有就创建,因为会更新session_key
    {
        $user = DB::table('users')->where('openid',$userInfo['openid'])->first();   //这里就是用openid，不用user_id
                                                    //code2session只返回openid，用这个判断是否已经存在
        if($user == null)
        {
            $userId = $this->createUser(['openid'   =>  $userInfo['openid'],
                                    'session_key'   =>  $userInfo['session_key']]);
        }
        else
        {
            DB::table('users')->where('openid',$userInfo['openid'])->update(['openid'   =>  $userInfo['openid'],
                                                                        'session_key'   =>  $userInfo['session_key']]);
            $userId = $user->user_id;
        }
        return $userId;
    }

    public function updatePhoneUser($userInfo)  //  手机号登录用    ——    有则调用更新,没有就创建
    {
        $user = DB::table('users')->where('phone',$userInfo['phone'])->first();
        if($user == null)
        {
            $userId = $this->createUser($userInfo);
        }
        else
        {
            $userId = $user->user_id;
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
    public function setUserInfo($userInfo,$data)
    {

        DB::table('users')->where('user_id',$userInfo->user_id)->update($data);
    }

    public function setName($data,$user_id)
    {
        DB::table('users')->where('user_id',$user_id)->update([
            'nickname'  =>  $data['nickname'],
            'avatar_url'    =>  $data['avatar_url']
        ]);
    }

    public function getUserInfo($userInfo)      //获取自己身材等信息
    {
        $detail = DB::table('users')->where('user_id',$userInfo->user_id)
                                    ->select('user_id','phone', 'avatar_url','nickname','height','weight','signature',
                                            'liked','birth_year as age','followers','following')->first();
        if ($detail->age > 0)
        {
            $detail->age = Carbon::now()->year - $detail->age;      //  year to age
        }
        return $detail;
    }

    public function getOthersInfo($user_id)      //获取他人的身材等信息
    {
        $hide = DB::table('users')->where('user_id',$user_id)->pluck('hide_figure');
//        return $hide[0];
        if ($hide[0] == 1)         //隐藏
        {
            $detail = DB::table('users')->where('user_id',$user_id)->select('user_id','avatar_url','nickname','openid','signature','liked','birth_year as age')->first();
        }
        else                    //显示
        {
            $detail = DB::table('users')->where('user_id',$user_id)->select('user_id','avatar_url','nickname','openid','weight','height','signature','liked','birth_year as age')->first();
        }

        if ($detail->age > 0)
        {
            $detail->age = Carbon::now()->year - $detail->age;  //  year to age
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
            DB::beginTransaction();
            try {
                DB::table('follows')->insert([
                    'from'  =>  $from,
                    'to'    =>  $to,
                    'created_at'    =>  Carbon::now(),
                    'updated_at'    =>  Carbon::now()
                ]);
                DB::table('users')->where('user_id',$from)->increment('following');
                DB::table('users')->where('user_id',$to)->increment('followers');
                DB::commit();
                return 1;           //成功
            } catch ( Exception $e){
                echo $e->getMessage();
                DB::rollBack();
            }

        }
        else
        {
            return -2;          //已经关注
        }

    }

    public function deleteFollow($from,$to)
    {
        DB::beginTransaction();
        try {
//            dd($from,$to);
            DB::table('follows')->where([
                'from'  =>  "$from",
                'to'    =>  $to
            ])->delete();
            DB::table('users')->where('user_id',$from)->decrement('following');
            DB::table('users')->where('user_id',$to)->decrement('followers');
            DB::commit();
        } catch ( Exception $e){
            echo $e->getMessage();
            DB::rollBack();
        }

    }

    public static function checkIfFollowed($from,$to)
    {
        $data = DB::table('follows')->where([
            'from'  =>  $from,
            'to'    =>  $to
        ])->first();
        return ($data == null);   //没关注返回1
    }

    public function getAllFollowed($user_id)
    {
        $data = DB::table('follows')->where([
            'to'  =>  $user_id
        ])  ->join('users','users.user_id','=','follows.from')
            ->select('follows.from as user_id','users.nickname','users.avatar_url','users.followers','users.following')
            ->orderBy('follows.created_at', 'desc')
            ->paginate(30);
        return $data;
    }

    public function getAllFollowing($user_id)
    {
        $data = DB::table('follows')->where([
            'from'  =>  $user_id
        ])  ->join('users','users.user_id','=','follows.to')
            ->select('follows.to as user_id','users.nickname','users.avatar_url','users.followers','users.following')
            ->orderBy('follows.created_at', 'desc')
            ->paginate(30);
        return $data;
    }

    public function getNicknameByUserId($user_id)
    {
        $data = DB::table('users')->where('user_id',$user_id)->pluck('nickname');
        return $data[0];
    }

    public function getConfig($user_id)
    {
        $data = DB::table('users')->where('user_id',$user_id)->select('hide_figure')->get();
        return $data;
    }

    public function setConfig($user_id,$choice)
    {
        DB::table('users')->where('user_id',$user_id)->update([
            'hide_figure' => $choice
        ]);
    }

    /**
     * @param $phone
     * @return mixed 无用户为null
     */
    public function getUserByPhone($phone)
    {
        $user = DB::table('users')->where('phone',$phone)->first();
        return $user;
    }

}