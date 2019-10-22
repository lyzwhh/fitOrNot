<?php
/**
 * Created by PhpStorm.
 * User: yuse
 * Date: 19/10/22
 * Time: 下午8:50
 */

namespace App\Services;


use App\Tools\Alipay;
use Illuminate\Http\Request;

class AlipayService
{
    private $aop;

    public function __construct(Alipay $pay)
    {
        $this->aop = $pay->client;
    }

    public function makeOrder(Request $request)
    {
        $outTradeNo = date('YmdHis') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $tradeData = [
            'out_trade_no'=>$outTradeNo,
            'total_amount'=> 8.88,
            'subject'=>'stone',
            'body'=>'上好的宝石一枚！'
        ];

        $request = new \AlipayTradeAppPayRequest();

        $request->setBizContent(json_encode($tradeData));
        $request->setNotifyUrl(AlipayConfig::notiryUrl);
        try{
            $res = $this->aop->sdkExecute($request);
        }catch (\Exception $exception){
            return response()->json([
                'code'=> 8888,
                'message'=>'订单创建出现错误，请联系开发人员调试'
            ]);
        }

        return response()->json([
            'code'=> 0,
            'data'=> $res
        ]);
    }
}