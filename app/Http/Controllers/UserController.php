<?php

namespace App\Http\Controllers;

use App\Services\MomentService;
use App\Services\RedisService;
use App\Services\SMSService;
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
    private $SMSService;
    public function __construct(WxxcxService $wxxcxService,
                                TokenService $tokenService,
                                UserService $userService,
                                MomentService $momentService,
                                SMSService $SMSService)
    {
        $this->wxxcxService = $wxxcxService;
        $this->tokenService = $tokenService;
        $this->userService = $userService;
        $this->momentService = $momentService;
        $this->SMSService = $SMSService;
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
                'user_id'   =>  $userId,
                'openid'   =>  $userInfo['openid'],
                'session_key'   =>  $userInfo['session_key']
            ]

        ]);
    }

    public function setUserInfo(Request $request)   //figure && signature  ( && what every in users table
    {
//        $this->validate($request,[
//            'signature' =>  'required'
//        ]);
//        $userInfo = $request->user;
//        $figure = $request['figure'];
////        return $request->all();
//        $this->userService->setUserInfo($userInfo,$request['figure'],$request['signature']);

        $userInfo = $request->user;
        $data = $request['data'];
//        return response([
//            'data'  =>  $data
//        ]);
        $this->userService->setUserInfo($userInfo,$data);
        return response([
            'errrcode'  =>  0
        ]);

    }

    public function setName(Request $request)
    {
        $userInfo = $request['user'];
        $data = $request['data'];
        $this->userService->setName($data,$userInfo->user_id);
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
    public function getOthersInfo($user_id)
    {
        $detail = $this->userService->getOthersInfo($user_id);
        $moment = $this->momentService->getMomentByUserId($user_id);
        $data = array();
        $data['userInfo'] = $detail;
        $data['moment'] = $moment;
        return response([
            'errcode'   =>  0,
            'data'  =>  $data
        ]);
    }

    public function createFollow($user_id,Request $request)
    {
        $flag = $this->userService->createFollow($request['user']->user_id,$user_id);
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

    public function deleteFollow($user_id,Request $request)
    {
        $this->userService->deleteFollow($request['user']->user_id,$user_id);
        return response([
            'errcode'   =>  '0',
            'errmsg'    =>  '取消关注成功'
        ]);
    }

    public function checkIfFollowed($user_id,Request $request)
    {
        $result = $this->userService->checkIfFollowed($request['user']->user_id,$user_id);
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
        $data = $this->userService->getAllFollowed($request['user']->user_id);
        return response([
            'errcode'   =>  0,
            'data'  =>$data
        ]);
    }

    public function getNicknameByUserId($user_id)
    {
        $data = $this->userService->getNicknameByUserId($user_id);
        return response([
            'errcode'   =>0,
            'data'      =>$data
        ]);
    }

    public function getConfig(Request $request)
    {
        $data = $this->userService->getConfig($request['user']->user_id);
        return response([
            'errcode'   =>  0,
            'data'  =>  $data[0]
        ]);
    }

    public function setConfig($choice,Request $request)
    {
        $this->userService->setConfig($request['user']->user_id,$choice);
        return response([
            'errcode'   =>  0
        ]);
    }

    public function getVCode(Request $request)
    {
        $this->validate($request,[
            'phone' =>  [
                'required',
                'regex:/^1\d{10}$/'     //手机号不断开放,懒得维护正则
            ]
        ]);
        $phone = $request['phone'];
        if (RedisService::checkPhoneFreq($phone))
        {
            return response([
                'errcode'   =>  -1,
                'errmsg'    =>  "请求太快,60s内只能发送一条短信"
            ]);
        }
        if (RedisService::checkCache(RedisService::getPrefix_1().$phone))
        {
            $vCode = RedisService::getCache(RedisService::getPrefix_1().$phone);
            $vCode = json_decode($vCode)->vCode;
        }
        else
        {
            $vCode = $this->SMSService->createVerificationCode();
            RedisService::setPhone($phone,$vCode);
        }
        $this->SMSService->sendSMS($phone,$vCode);

        return response([
            'errcode'   =>  0
        ]);

    }
}
