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

    public function updateUser($userInfo)
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
}