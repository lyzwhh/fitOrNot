<?php
/**
 * Created by PhpStorm.
 * User: yuse
 * Date: 19/10/22
 * Time: 下午8:24
 */
return [

    //应用ID,您的APPID。
    'ALIPAY_APP_ID' => env('ALIPAY_APP_ID'),

    //商户私钥，您的原始格式RSA私钥
    'ALIPAY_RSA_PRIVATE_KEY' => env('ALIPAY_RSA_PRIVATE_KEY'),

    //异步通知地址
    'notify_url' => env("ALIPAY_NOTIFY_URL"),
    //http://工程公网访问地址/alipay.trade.wap.pay-PHP-UTF-8/notify_url.php

    //同步跳转
    'return_url' => env("ALIPAY_RETURN_URL"),
    //http://mitsein.com/alipay.trade.wap.pay-PHP-UTF-8/return_url.php
    // jk.mrwangqi.com

    //支付宝网关
    'ALIPAY_GATEWAY_URL' => env('ALIPAY_GATEWAY_URL'),

    //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
    'ALIPAY_ZFB_PUBLIC_KEY' => env('ALIPAY_ZFB_PUBLIC_KEY'),
];