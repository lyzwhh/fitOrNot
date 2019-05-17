<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Services\WxxcxService;
use App\Services\TokenService;
use App\Services\UserService;
class UserController extends Controller
{
    private $wxxcxService;
    private $tokenService;
    private $userService;
    public function __construct(WxxcxService $wxxcxService,
                                TokenService $tokenService,
                                UserService $userService)
    {
        $this->wxxcxService = $wxxcxService;
        $this->tokenService = $tokenService;
        $this->userService = $userService;
    }

    public function code2session(Request $request)
    {
        $this->validate($request,[
            'code' => 'required'
        ]);
        $loginInfo = $request->all();
        $userInfo = $this->wxxcxService->login($loginInfo['code']);   //包含open_id,session_key,(unionid),
                                                                    //errcode,errmsg
        if ($userInfo['errcode'] != 0)
        {
            return response(['errcode' => $userInfo['errcode']
                            ,'errmsg' => $userInfo['errmsg']]);
        }
        $userId = $this->userService->updateUser($userInfo);
        $token = $this->tokenService->makeToken($userId);
        return response([
            'errcode'  =>  0,
            'data'  =>  [
                'token' =>  $token,
                'open_id'   =>  $userInfo['open_id'],
                'session_key'   =>  $userInfo['session_key']
            ]

        ]);
    }
}