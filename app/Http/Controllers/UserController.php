<?php

namespace App\Http\Controllers;

use App\Services\MomentService;
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
    private $momentService;
    public function __construct(WxxcxService $wxxcxService,
                                TokenService $tokenService,
                                UserService $userService,
                                MomentService $momentService)
    {
        $this->wxxcxService = $wxxcxService;
        $this->tokenService = $tokenService;
        $this->userService = $userService;
        $this->momentService = $momentService;
    }

    public function code2session(Request $request)
    {
        $this->validate($request,[
            'code' => 'required'
        ]);
        $loginInfo = $request->all();
        $userInfo = $this->wxxcxService->login($loginInfo['code']);   //包含openid,session_key,(unionid),
                                                                    //errcode,errmsg
        if ($userInfo['errcode'] != 0)
        {
            return response($userInfo);
        }
//        return response($userInfo);
        $userId = $this->userService->updateUser($userInfo);
        $token = $this->tokenService->makeToken($userId);
        return response([
            'errcode'  =>  0,
            'data'  =>  [
                'token' =>  $token,
                'openid'   =>  $userInfo['openid'],
                'session_key'   =>  $userInfo['session_key']
            ]

        ]);
    }

    public function setUserInfo(Request $request)   //figure && signature
    {
        $this->validate($request,[
            'signature' =>  'required'
        ]);
        $userInfo = $request->user;
        $figure = $request['figure'];
//        return $request->all();
        $this->userService->setUserInfo($userInfo,$request['figure'],$request['signature']);

        return response([
            'errrcode'  =>  0
        ]);

    }

    public function setName(Request $request)
    {
        $userInfo = $request['user'];
        $data = $request['data'];
        $this->userService->setName($data,$userInfo->openid);
        return response([
            'errcode'   =>  0
        ]);
    }


    public function getUserInfo(Request $request)
    {
        $userInfo = $request->user;
        $detail = $this->userService->getUserInfo($userInfo);
        return response([
            'errcode'  =>   0,
            'data'  =>  $detail
        ]);
    }
    public function getOthersInfo($openid)
    {
        $detail = $this->userService->getOthersInfo($openid);
        $moment = $this->momentService->getMomentByOpenid($openid);
        $data = array();
        $data['userInfo'] = $detail;
        $data['moment'] = $moment;
        return response([
            'errcode'   =>  0,
            'data'  =>  $data
        ]);
    }

    public function createFollow($openid,Request $request)
    {
        $flag = $this->userService->createFollow($request['user']->openid,$openid);
        if ($flag == 1)
        {
            return response([
                'errcode'   =>0,
                'errmsg'    =>  '关注成功'
            ]);
        }
        else if ($flag == -1)
        {
            return response([
                'errcode'   =>  -1,
                'errmsg'    =>  '不能关注自己'
            ]);
        }
        else if ($flag == -2)
        {
            return response([
                'errcode'   =>  -2,
                'errmsg'    =>  '已经关注,不能重复关注'
            ]);
        }

    }

    public function deleteFollow($openid,Request $request)
    {
        $this->userService->deleteFollow($request['user']->openid,$openid);
        return response([
            'errcode'   =>  '0',
            'errmsg'    =>  '取消关注成功'
        ]);
    }

    public function checkIfFollowed($openid,Request $request)
    {
        $result = $this->userService->checkIfFollowed($request['user']->openid,$openid);
        if ($result == 1)
        {
            return response([
                'errcode'  =>0      //表示没有关注,能够进行关注
            ]);
        }
        else
        {
            return response([
                'errcode' =>-1      //已经关注
            ]);
        }
    }

    public function getAllFollowed(Request $request)
    {
        $data = $this->userService->getAllFollowed($request['user']->openid);
        return response([
            'errcode'   =>  0,
            'data'  =>$data
        ]);
    }

    public function getNicknameByOpenid($openid)
    {
        $data = $this->userService->getNicknameByOpenid($openid);
        return response([
            'errcode'   =>0,
            'data'      =>$data
        ]);
    }

    public function getConfig(Request $request)
    {
        $data = $this->userService->getConfig($request['user']->openid);
        return response([
            'errcode'   =>  0,
            'data'  =>  $data
        ]);
    }

    public function setConfig($choice,Request $request)
    {
        $this->userService->setConfig($request['user']->openid,$choice);
        return response([
            'errcode'   =>  0
        ]);
    }
}
