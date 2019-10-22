<?php

namespace App\Http\Controllers;

use App\Services\AlipayService;
use App\Tools\Alipay;
use Illuminate\Http\Request;

class PayController extends Controller
{
    private $aliPay;
    private $alipayService;


    public function __construct(Alipay $alipay,AlipayService $alipayService)
    {
        $this->aliPay = $alipay->client;
        $this->alipayService = $alipayService;
    }

//    public function AliPay(Request $request)
//    {
//        $this->alipayService->makeOrder($request);
//        return response([
//            'code'  =>  0
//        ]);
//    }

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
        $request->setNotifyUrl(config('alipay.notify_url'));
        try{
            $res = $this->aliPay->sdkExecute($request);
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

    public function AlipayNotify()
    {
        try{
            $flag = $this->aliPay->rsaCheckV1($_POST,NULL,$this->aliPay->signType);
        }catch (\Exception $exception){
//            LogHelper::make([
//                'notify_check_sign'=>'error'
//            ],'trade');
            return 'error';
        }

        if ($flag){
            $post = $_POST;
            $post['pay_type'] = 'alipay';
            $post['check_sign'] = 'success';
//            LogHelper::make($post,'trade');
            $order_str = $_POST['out_trade_no'];
            $status = $_POST['trade_status'];
            if ($status == 'TRADE_SUCCESS'|| $status == 'TRADE_FINISHED'){
                $this->goodsService->finishOrder($order_str,2);
            }elseif ($status == 'TRADE_CLOSED'){
                $this->goodsService->finishOrder($order_str,1);
            }
            return 'success';
        }else{
//            LogHelper::make([
//                'notify_check_sign'=>'fail'
//            ],'trade');
            return '';
        }
    }
}
