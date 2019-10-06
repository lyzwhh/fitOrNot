<?php

namespace App\Http\Controllers;

use App\Services\ClothesService;
use App\Services\WxxcxService;
use Illuminate\Http\Request;
use App\Tools\ValidatorHelper;

class ClothesController extends Controller
{
    //
    private $clothesService;
    private $wxxcxService;

    public function __construct(ClothesService $clothesService,WxxcxService $wxxcxService)
    {
        $this->clothesService = $clothesService;
        $this->wxxcxService = $wxxcxService;
    }

    public function setClothes(Request $request)
    {
//        $this->validate($request,[
//            'clothes.*.pic_url' =>  'required'
//        ]);
//        $clothesInfo = $request->all();
//        $userInfo = $clothesInfo['user'];
//        foreach ($clothesInfo['clothes'] as $clothes)
//        {
//            $this->clothesService->setClothes($userInfo->user_id,$clothes);
//        }
        $rules = [
            'pic_url'   =>  'required',
            'category'  =>  'required',
            'brand'     =>  'sometimes',
            'color'     =>  'sometimes',
            'tags'      =>  'sometimes',
            'remarks'   =>  'sometimes'
        ];
        $setData = ValidatorHelper::checkAndGet($request['clothes'],$rules);
        $this->clothesService->setClothes($request['user']->user_id,$setData);
        return response([
            'errcode'  =>  0
        ]);
    }

//    public function getClothes(Request $request)  //小程序用1
//    {
//        $userInfo = $request['user'];
//        $clothes = $this->clothesService->getOrderClothes($userInfo->user_id);
//
//        return response([
//            'errode'  =>  0,
//            'flag'  =>  0,      //小程序端要求 , flag为1时代表返回的是套装 , 0 为单品
//            'data'  =>  $clothes
//        ]);
//    }
//
//    public function getClothes2(Request $request)   //小程序用2
//    {
//        $userInfo = $request['user'];
//        $clothes = $this->clothesService->getOrderClothes2($userInfo->user_id);
//
//        return response([
//            'errode'  =>  0,
//            'flag'  =>  0,
//            'data'  =>  $clothes
//        ]);
//    }

    /**
     * @param Request $request
     * 获取自己所有衣服
     */
    public function getClothes(Request $request)    //app用
    {
        $userInfo = $request['user'];
        $clothes = $this->clothesService->getAllClothes($userInfo->user_id);
        return response([
            'errcode'   =>  0,
            'data'      =>  $clothes
        ]);
    }

    public function getClothesByWord(Request $request)
    {
        $userInfo = $request['user'];
        $word = $request['word'];
        $clothes = $this->clothesService->getClothesByWord($userInfo->user_id,$word);
        return response([
            'errcode'   =>  0,
            'data'      =>  $clothes
        ]);
    }

    public function updateClothes(Request $request)
    {
        $rules = [
            'id'        =>  'required',
            'pic_url'   =>  'sometimes',
            'category'  =>  'sometimes',
            'brand'     =>  'sometimes',
            'color'     =>  'sometimes',
            'tags'      =>  'sometimes',
            'remarks'   =>  'sometimes'
        ];

        $setData = ValidatorHelper::checkAndGet($request['clothes'],$rules);
        $userInfo = $request['user'];
        if ($this->clothesService->updateClothes($setData,$userInfo->user_id) == -1)
        {
            return response([
                'errcode'  =>  -1,
                'errmsg'   =>  "非衣服主人,无法修改"
            ]);
        }

        return response([
            'errode'  =>  0
        ]);
    }

    public function deleteClothes($id,Request $request)
    {
        $userInfo = $request['user'];
        if ($this->clothesService->getClothesOwnerById($id) != $userInfo->user_id)
        {
            return response([
                'errcode'  =>  -1,
                'errmsg'   =>  "非衣服主人,无法修改"
            ]);
        }
        else
        {
            $this->clothesService->deleteClothes($id,$userInfo->user_id);
        }
        return response([
            'errcode'   =>   0,
            'errmsg'    =>  '删除完成'
        ]);
    }

    public function setSuit(Request $request)       //todo title默认值处理 , tags
    {
//        dd($request);
        $rule = [
            "clothes"   =>  "required",
            "category"  =>  "required",
            "title"     =>  "sometimes",
            "tags"      =>  "sometimes",
            "remarks"   =>  "sometimes",
            "background"=>  "sometimes",
            "clothes_ids"=> "sometimes"
        ];
        $userInfo = $request['user'];
        $suitInfo = $request['suit'];
        $setData = ValidatorHelper::checkAndGet($suitInfo,$rule);
        $setData['clothes_ids'] = implode(',',$setData['clothes_ids']);
        $flag = $this->clothesService->setSuit($setData,$userInfo,$suitInfo['clothes_ids']);
        if ($flag != 0)
        {
            return response([
                'errcode'   =>  -1,
                'errmsg'    =>  "添加失败"
            ]);
        }
        return response([
            'errcode'   =>  0
        ]);

    }

    public function getSuit(Request $request)
    {
        $data = $this->clothesService->getAllSuit($request['user']->user_id);

        return response([
            'errcode'   =>  0,
            'data'  =>  $data
        ]);
    }

    public function getSuitByWord(Request $request)
    {
        $userInfo = $request['user'];
        $word = $request['word'];
        $clothes = $this->clothesService->getSuitByWord($userInfo->user_id,$word);
        return response([
            'errcode'   =>  0,
            'data'      =>  $clothes
        ]);
    }

    public function deleteSuit($suitId,Request $request)
    {
        $userInfo = $request['user'];
        if ($userInfo->user_id != $this->clothesService->getSuitOwner($suitId))
        {
            return response([
                'errcode'   =>  -1,
                'errmsg'    =>  '非该套装主人'
            ]);
        }
        $flag = $this->clothesService->deleteSuit($suitId,$userInfo);
        if ($flag == 0)
        {
            return response([
                'errcode'   =>  0
            ]);
        }
        else
        {
            return response([
                'errcode'   =>  -1,
                'errmsg'    =>  "删除失败"
            ]);
        }

    }

    public function wearSuit($suitId,Request $request)  //删了它
    {
        $userInfo = $request['user'];
//        dd($this->clothesService->getSuitOwner($suitId));
        if ($userInfo->user_id != $this->clothesService->getSuitOwner($suitId))
        {
            return response([
                'errcode'   =>  -1,
                'errmsg'    =>  '非该套装主人'
            ]);
        }
        $this->clothesService->wearSuit($suitId);
        return response([
            'errcode'   =>  0
        ]);

    }





    public function createSRequest(Request $request)
    {
        $rule = [
            'request_to'    =>  'required',
            'order_msg' =>  'required'
        ];
        $userInfo = $request['user'];
        $setData = ValidatorHelper::checkAndGet($request['SRequest'],$rule);
        $setData['request_from'] = $userInfo->user_id;
        $this->clothesService->createSRequest($setData);
        return response([
            'errcode'   =>  0
        ]);
    }

    public function getAllMySRing(Request $request)
    {
        $userInfo = $request['user'];
        $data = $this->clothesService->getAllMySRing($userInfo->user_id);
        return response([
            'errcode'   =>  0,
            'data'  =>$data
        ]);
    }

    public function getAllMySRed(Request $request)
    {
        $userInfo = $request['user'];
        $data = $this->clothesService->getAllMySRed($userInfo->user_id);
        return response([
            'errcode'   =>  0,
            'data'  =>  $data
        ]);
    }

    public function getAllClothesBySR($request_id,Request $request)
    {
        $userInfo = $request['user'];
        $SR = $this->clothesService->getToSR($request_id,$userInfo->user_id);
        if ($SR == null)
        {
            return response([
                'errcode'   =>  -1,
                'errmsg'    =>  "搭配请求信息有误"
            ]);
        }
        elseif ($SR->request_status == 1)
        {
            return response([
                'errcode'   =>  -1,
                'errmsg'    =>  "搭配请求已完成"
            ]);
        }

        $clothes = $this->clothesService->getAllClothesBySR($SR);
        return response([
            'errcode'   =>  0,
            'data'  =>  $clothes
        ]);

    }

    public function setSuitBySR(Request $request)
    {
        $rule = [
            "clothes"   =>  "required",
            "category"  =>  "required",
            "title"     =>  "sometimes",
            "tags"      =>  "sometimes",
//            "remarks"   =>  "sometimes",      //不能备注，删除
            "background"=>  "sometimes",
            "clothes_ids"=> "sometimes",
//            "feed_back" =>  "sometimes"       //request里有 ， setData不要
            "request_id"    =>  "required"
        ];
        $userInfo = $request['user'];
        $suitInfo = $request['suit'];
        $setData = ValidatorHelper::checkAndGet($suitInfo,$rule);
        $setData['clothes_ids'] = implode(',',$setData['clothes_ids']);
        $SR = $this->clothesService->getToSR($setData['request_id'],$userInfo->user_id);
        if ($SR->request_status != 0)
        {
            return response([
                'errcode'   =>  -1,
                'errmsg'    =>  "该搭配请求已完成"
            ]);
        }
        $flag = $this->clothesService->setSuitBySR($setData,$suitInfo['clothes_ids'],$SR , $suitInfo['feed_back']);
        if ($flag != 0)
        {
            return response([
                'errcode'   =>  -1,
                'errmsg'    =>  "添加失败"
            ]);
        }
        return response([
            'errcode'   =>  0
        ]);
    }

    public function getSuitBySR($request_id,Request $request)
    {
        $userInfo = $request['user'];
        $SR = $this->clothesService->getFromSR($request_id,$userInfo->user_id);
        $suit = $this->clothesService->getSuitBySR($SR);

        return response([
            'errcode'   =>  0,
            'data'      =>  $suit
        ]);

    }



}
