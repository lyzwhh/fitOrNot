<?php

namespace App\Http\Controllers;

use App\Services\MomentService;
use App\Services\UserService;
use Illuminate\Http\Request;

class MomentController extends Controller
{
    private $momentService;
    private $userService;

    public function __construct(MomentService $momentService ,UserService $userService)
    {
        $this->momentService = $momentService;
        $this->userService = $userService;
    }

    public function createMoment(Request $request)
    {
        $this->validate($request,[
            'pics_url'  =>  'required'
        ]);
        $momentInfo['pics_url'] = json_encode($request['pics_url']);
        $momentInfo['content'] = $request['content'];
        $momentInfo['writer'] = $request['user']->openid;

        $this->momentService->createMoment($momentInfo);
        return response([
            'errcode'   => 0
        ]);
    }

    public function getMoment()
    {
        $momentDate = $this->momentService->getNewestMoment();
        foreach ($momentDate as $m)
        {
            $m->pics_url = json_decode($m->pics_url);
//            $m->writer = $this->userService->getIdByOpenid($m->writer);
        }

        return response([
            'errcode' => 0,
            'data' => $momentDate
        ]);
    }

    public function deleteMoment($id)
    {

    }

    public function getMomentDetail($id)   //删除按钮是否要放在这,是否要识别是不是自己的moment
    {

    }

}
