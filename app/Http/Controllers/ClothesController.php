<?php

namespace App\Http\Controllers;

use App\Services\ClothesService;
use App\Services\WxxcxService;
use Illuminate\Http\Request;

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
        $this->validate($request,[
            'clothes.*.pic_url' =>  'required'
        ]);
        $clothesInfo = $request->all();
        $userInfo = $clothesInfo['user'];
        foreach ($clothesInfo['clothes'] as $clothes)
        {
            $this->clothesService->setClothes($userInfo->openid,$clothes);
        }

        return response([
            'errcode'  =>  0
        ]);
    }

    public function getClothes(Request $request)
    {
        $userInfo = $request['user'];
        $clothes = $this->clothesService->getOrderClothes($userInfo->openid);

        return response([
            'errode'  =>  0,
            'flag'  =>  0,
            'data'  =>  $clothes
        ]);
    }

    public function updateClothes(Request $request)
    {
        $this->validate($request,[
            'clothes.*.id' =>  'required'
        ]);
        $userInfo = $request['user'];
        $clothesInfo = $request['clothes'];
        foreach ($clothesInfo as $clothes)
        {
            if ($this->clothesService->updateClothes($clothes,$userInfo->openid) == -1)
            {
                return response([
                    'errcode'  =>  -1,
                    'errmsg'   =>  "非衣服主人,无法修改"
                ]);
            }
        }

        return response([
            'errode'  =>  0
        ]);
    }

    public function deleteClothes($id,Request $request)
    {
        $userInfo = $request['user'];
        if ($this->clothesService->checkOwner($id,$userInfo->openid) != 0)
        {
            return response([
                'errcode'  =>  -1,
                'errmsg'   =>  "非衣服主人,无法修改"
            ]);
        }
        else
        {
            $this->clothesService->deleteClothes($id);
        }
        return response([
            'errcode'   =>   0,
            'errmsg'    =>  '删除完成'
        ]);
    }

    public function setSuit(Request $request)
    {
//        dd($request);
        $userInfo = $request['user'];
        $suitInfo = $request['suit'];
        $this->clothesService->setSuit($suitInfo,$userInfo);

        return response([
            'errcode'   =>  0
        ]);

    }

    public function getSuit(Request $request)
    {
        $data = $this->clothesService->getSuit($request['user']->openid);

        return response([
            'errcode'   =>  0,
            'flag'  =>  1,
            'data'  =>  $data
        ]);
    }

    public function deleteSuit($suitId,Request $request)
    {
        $userInfo = $request['user'];
        if ($userInfo->openid != $this->clothesService->getSuitOwner($suitId))
        {
            return response([
                'errcode'   =>  -1,
                'errmsg'    =>  '非该套装主人'
            ]);
        }
        $this->clothesService->deleteSuit($suitId);
        return response([
            'errcode'   =>  0
        ]);
    }

    public function wearSuit($suitId,Request $request)
    {
        $userInfo = $request['user'];
//        dd($this->clothesService->getSuitOwner($suitId));
        if ($userInfo->openid != $this->clothesService->getSuitOwner($suitId))
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

}
