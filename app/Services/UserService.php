<?php
/**
 * Created by PhpStorm.
 * User: yuse
 * Date: 19/5/17
 * Time: 下午6:52
 */

namespace App\Services;
use Carbon\Carbon;

class UserService
{
    public function login()
    {

    }

    public function updateUser($userInfo)
    {
        $user = DB::table('users')->where('open_id',$userInfo['open_id'])->first();
        if($user == null)
        {
            $userId = $this->createUser($userInfo);
        }
        else
        {
            DB::table('users')->where('open_id',$userInfo['open_id'])->update($userInfo);
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