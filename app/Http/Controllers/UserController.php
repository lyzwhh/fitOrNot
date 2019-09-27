<?php

namespace App\Http\Controllers;

use App\Services\MomentService;
use App\Services\RedisService;
use App\Services\SMSService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Services\WxxcxService;
use App\Services\TokenService;
use App\Services\UserService;

use Illuminate\Support\Facades\Validator;class UserController extends Controller
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
        $rules = [
            'height' =>  'sometimes|required|integer|between:0,280',
            'weight' =>  'sometimes|required|integer|between:0,200',
            'hide_figure'   =>  'sometimes|boolean',
            'age'    =>  'sometimes|integer|between:0,150',
            'nickname'  =>  'sometimes',
            'signature' =>  'sometimes',
            'avatar_url'    =>  'sometimes',

        ];
        $data = $request['data'];
        $validator = Validator::make($data,$rules);
        if ($validator->fails())
        {
            return response([
                'errcode'   =>  -1,
                'errmsg'    =>  $validator->errors()
            ]);
        }

        $userInfo = $request->user;
        $setData = [];

        foreach ($rules as $key => $rule)   //筛取有用信息 , 防止修改phone等信息
        {
            if (isset($data[$key]))
            {
                if ($key == 'age')
                {
                    $setData['birth_year']  = Carbon::now()->year-$data[$key];      // age to year
                }
                else
                {
                    $setData[$key] = $data[$key];
                }
            }
        }

        $this->userService->setUserInfo($userInfo,$setData);
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
        $flag = $this->userService->checkIfFollowed($request['user']->user_id,$user_id);
        if ($flag)
        {
            return response([
                'errcode'   =>  -1,
                'errmsg'    =>  '未关注该用户'
            ]);
        }
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
//        if ($this->userService->getUserByPhone($phone) != null)
//        {
//            return response([
//                'errcode'   =>  -1,
//                'errmsg'    =>  '该手机号已经注册'
//            ]);
//        }
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
            $vCode = json_decode($vCode)->vCode;        // 重新设置时间
            RedisService::setPhone($phone,$vCode);
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

    public function registerByVCode(Request $request)   //叫做注册 , 但也登录
    {
        $this->validate($request,[
            'phone' =>  [
                'required',
                'regex:/^1\d{10}$/'     //手机号不断开放,懒得维护正则
            ],
            'VCode' =>  [
                'required'
            ]
        ]);
        $phone = $request['phone'];
        $VCode = $request['VCode'];

//        if ($this->userService->getUserByPhone($phone) != null) // 防止一个验证码注册多个账号用 , 因登录失效
//        {
//            return response([
//                'errcode'   =>  -1,
//                'errmsg'    =>  '该手机号已经注册'
//            ]);
//        }

        if (!RedisService::checkVCode($phone,$VCode))
        {
            return response([
                'errcode'   =>  -1,
                'errmsg'    =>  '验证码错误'
            ]);
        }

        $userInfo = [
            'phone' => $phone
        ];

        $first_register = $this->userService->getUserByPhone($phone);

        $user_id = $this->userService->updatePhoneUser($userInfo);
        $token = $this->tokenService->makeToken($user_id);

        RedisService::delVCode($phone);         //todo 删除后checkPhoneFreq 失效 , 可通过发短信 - 登录 - 发短信 - 登录 . 快速发短信 . throttle中间件限流

        return response([
            'errcode'   =>  0,
            'data'  =>  [
                'user_id'   =>  $user_id,
                'token'     =>  $token,
                'first_register'    =>  $first_register == null
            ]
        ]);
    }

    public function registerByWxPhone(Request $request)
    {
        $userInfo = $request['user'];
        $data = null;
        $result = $this->wxxcxService->decryptData($userInfo['user_id'],$request['encryptedData'],$request['iv'],$data);
        if ($result == -1)
        {
            return response([
                'errcode'   =>  -1,
                'errmsg'    =>  "session_key 不存在"
            ]);
        }
        else if ($result == -2)
        {
            return response([
                'errcode'   =>  -1,
                'errmsg'    =>  "iv有误"
            ]);
        }
        else if ($result == -3)
        {
            return response([
                'errcode'   =>  -1,
                'errmsg'    =>  "buffer非法"
            ]);
        }
        else if ($result == 0)
        {
            return response([
                'errcode'   =>  0,
                'data'  =>  $data
            ]);
        }
    }
}
