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
        $clothes = $this->clothesService->getClothes($userInfo->openid);

        return response([
            'errode'  =>  0,
            'data'  =>  $clothes,
            'AccessToken'  => $this->wxxcxService->getAccessToken()
        ]);
    }
}
