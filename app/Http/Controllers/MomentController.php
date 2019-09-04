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
        $momentInfo['writer'] = $request['user']->user_id;

        $this->momentService->createMoment($momentInfo);
        return response([
            'errcode'   => 0
        ]);
    }

    public function getMoment()
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
        $this->validate($request,[
            'comment.content'   =>  'required',
            'comment.to'    =>  'required'
        ]);
        $commentInfo = $request['comment'];
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
