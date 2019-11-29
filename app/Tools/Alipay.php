<?php
/**
 * Created by PhpStorm.
 * User: yuse
 * Date: 19/10/22
 * Time: 下午8:20
 */

namespace App\Tools;

include 'Alipay/AopSdk.php';

class Alipay
{
    public $client;
    public function __construct(\AopClient $aopClient)
    {
        $this->client = $aopClient;

        $this->client->gatewayUrl = config('alipay.ALIPAY_GATEWAY_URL');
        $this->client->appId = config('alipay.ALIPAY_APP_ID');
        $this->client->rsaPrivateKey = config('alipay.ALIPAY_RSA_PRIVATE_KEY');
        $this->client->alipayrsaPublicKey = config('ALIPAY_ZFB_PUBLIC_KEY');
        $this->client->apiVersion = 1.0;
        $this->client->signType = "RSA2";
        $this->client->postCharset = 'UTF-8';
        $this->client->format = "json";

    }
}