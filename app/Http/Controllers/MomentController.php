<?php

namespace App\Http\Controllers;

use App\Services\ClothesService;
use App\Services\MomentService;
use App\Services\UserService;
use Illuminate\Http\Request;
use App\Tools\ValidatorHelper;

class MomentController extends Controller
{
    private $momentService;
    private $userService;
    private $clothesService;
    public function __construct(MomentService $momentService ,UserService $userService,ClothesService $clothesService)
    {
        $this->momentService = $momentService;
        $this->userService = $userService;
        $this->clothesService = $clothesService;
    }

    public function createMoment(Request $request)
    {
        $rules = [
            'suit_id'  =>  'required',
            'content'   =>  'required'
        ];
        $setData = ValidatorHelper::checkAndGet($request->all(),$rules);
        $setData['writer'] = $request['user']->user_id;
        $owner = $this->clothesService->getSuitOwner($setData['suit_id']);
        if ($owner != $setData['writer'])
        {
            return response([
                'errcode'   =>  -1,
                'errmsg'    =>  "非该搭配所有者"
            ]);
        }
//        dd($setData);
        $this->momentService->createMoment($setData);
        return response([
            'errcode'   => 0
        ]);
    }

    public function getMoment()     //todo 排序
    {
        $momentData = $this->momentService->getNewestMoment();

        return response([
            'errcode' => 0,
            'data' => $momentData
        ]);
    }

    public function deleteMoment($id,Request $request)
    {
        $userInfo = $request['user'];
        if ($userInfo->user_id != $this->momentService->getMomentOwner($id))
        {
            return response([
                'errcode'   =>  -1,
                'errmsg'    =>  '非评论主人无法删除'
            ]);
        }
        $this->momentService->deleteMoment($id);
        return response([
            'errcode'   =>  0,
            'errmsg'    =>  '删除完成'
        ]);

    }

    public function getMomentDetail($id)   //删除按钮是否要放在这,是否要识别是不是自己的moment
    {

    }

    public function getMomentByUserId($user_id)
    {
        $data = $this->momentService->getMomentByUserId($user_id);
        return response([
            'errcode'   =>  0,
            'data'  =>  $data
        ]);
    }

    public function createLike($id,Request $request)        //id为moment的id
    {
        $result = $this->momentService->createLike($request['user']->user_id,$id);
        if($result == 1)
        {
            return response([
                'errcode'   =>  0
            ]);
        }
        else if ($result == -1)
        {
            return response([
                'errcode'   =>   -1,
                'errmsg'    =>  '不可重复like'
            ]);
        }
    }

    public function deleteLike($id,Request $request)
    {
        $this->momentService->deleteLike($request['user']->user_id,$id);
        return response([
            'errcode'   =>  0
        ]);
    }

    public function checkIfLiked($id,Request $request)
    {
        $result = $this->momentService->checkIfLiked($request['user']->user_id,$id);

        if ($result == 1)
        {
            return response([
                'errcode'  =>0      //表示没有like,能够进行like
            ]);
        }
        else
        {
            return response([
                'errcode' =>-1      //已经like
            ]);
        }
    }

    public function createComment(Request $request)
    {
        $rule = [
            'content'   =>  'required',
            'to'    =>  'required'
        ];
        $commentInfo = $request['comment'];
        $setData = ValidatorHelper::checkAndGet($commentInfo,$rule);
//        dd($setData);
        if ($this->momentService->getMomentById($setData['to']) == null)
        {
            return response([
                'errcode'   =>  -1,
                'errmsg'    =>  "moment不存在"
            ]);
        }
        $commentInfo['from'] = $request['user']->user_id;
        $this->momentService->createComment($commentInfo);
        return response([
            'errcode'   =>  0
        ]);
//        return $request->all();

    }

    public function deleteComment($commentId,Request $request)
    {
        $userInfo = $request['user'];
//        return response([
//            "data"  =>  collect([]) == null
//            ]
//        );
        if ($userInfo->user_id != $this->momentService->getCommentOwner($commentId) && $userInfo->user_id != null)
        {
            return response([
                'errcode'  =>  -1,
                'errmsg'    =>  '非评论主人'
            ]);
        }
        $this->momentService->deleteComment($commentId);
        return response([
            'errcode'   =>  0
        ]);
    }

    public function getCommentByMoment($momentId)
    {
        $data = $this->momentService->getCommentByMoment($momentId);

        return response([
            'errcode'   =>  0,
            'data'  =>  $data
        ]);
    }

}
