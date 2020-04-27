<?php

namespace App\Http\Controllers;

use Yansongda\Pay\Pay;
use Yansongda\Pay\Log;

class PayController2
{
    protected $config = [
        'app_id' => '2016082000295641',
        'notify_url' => '',
        'return_url' => '', //暂无
        'ali_public_key' => '',
        // 加密方式： **RSA2**
        'private_key' => '',
        'log' => [ // optional
            'file' => './logs/alipay.log',
            'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
            'type' => 'single', // optional, 可选 daily.
            'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
        ],
        'http' => [ // optional
            'timeout' => 5.0,
            'connect_timeout' => 5.0,
            // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
        ],
        'mode' => 'dev', // optional,设置此参数，将进入沙箱模式
    ];

    public function loadConfig($method)
    {
        if ($method == "alipay")
        {
            $this->config['ali_public_key'] = config('alipay.ALIPAY_ZFB_PUBLIC_KEY');
            $this->config['private_key'] = config('alipay.ALIPAY_RSA_PRIVATE_KEY');
            $this->config['notify_url'] = config('alipay.notify_url');
            $this->config['app_id'] = config('alipay.ALIPAY_APP_ID');
            $this->config['return_url'] = config('alipay.return_url');
        }
    }

    public function alipay()
    {
        $this->loadConfig('alipay');
        $order = [
            'out_trade_no' => time(),
            'total_amount' => '3.14',
            'subject' => 'test subject - 测试',
        ];

        $alipay = Pay::alipay($this->config)->web($order);
//        dd($alipay);
//        dd($this->config);
        return $alipay;// laravel 框架中请直接 `return $alipay`
    }

    public function return()
    {
        $this->loadConfig('alipay');
        $data = Pay::alipay($this->config)->verify(); // 是的，验签就这么简单！

        // 订单号：$data->out_trade_no
        // 支付宝交易号：$data->trade_no
        // 订单总金额：$data->total_amount
        dd("成功啦啊 啊 啊啊");
    }

    public function alipaynotify()
    {
        $this->loadConfig('alipay');
        $alipay = Pay::alipay($this->config);

        try{
            $data = $alipay->verify(); // 是的，验签就这么简单！

            // 请自行对 trade_status 进行判断及其它逻辑进行判断，在支付宝的业务通知中，只有交易通知状态为 TRADE_SUCCESS 或 TRADE_FINISHED 时，支付宝才会认定为买家付款成功。
            // 1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号；
            // 2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额）；
            // 3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）；
            // 4、验证app_id是否为该商户本身。
            // 5、其它业务逻辑情况

            Log::debug('Alipay notify', $data->all());
        } catch (\Exception $e) {
            // $e->getMessage();
        }

        return $alipay->success();// laravel 框架中请直接 `return $alipay->success()`
    }
}