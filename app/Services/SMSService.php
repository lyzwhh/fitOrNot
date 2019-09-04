<?php
/**
 * Created by PhpStorm.
 * User: yuse
 * Date: 19/9/4
 * Time: 下午4:59
 */


namespace App\Services;

class SMSService
{
    /**
     * 发送短信
     * @param string $phone
     * @param string $verificationCode
     * @param int $limit_time   验证码有效时间, 分钟, 不填默认30
     */
    public static function sendSMS(string $phone, string $verificationCode, int $limit_time = 30)
    {

        $prefix = '【搭不搭】';
        $messageModel = $prefix . "您的验证码是 ：\t\n%s\t\n请在%d分钟内填写";
        $newMsg = sprintf($messageModel, $verificationCode, $limit_time);
        $newMsg = urlencode($newMsg);
        $url = "http://zapi.253.com/msg/HttpBatchSendSM?"
            . http_build_query([
                'account'   => env('SMS_ACCOUNT'),
                'pswd'      => env('SMS_PASSWORD'),
                'mobile'    => $phone
            ])
            . "&msg=" . $newMsg;

        self::doCurlGetRequest($url);
    }

    /**
     * 创建一个5位验证码
     * @return string
     */
    public static function createVerificationCode()
    {
        return sprintf('%05d', mt_rand(1, 99999));
    }

    /**
     * @param string $url
     * @return mixed|string
     */
    private static function doCurlGetRequest(string $url)
    {
        $con = curl_init();
        curl_setopt($con, CURLOPT_URL, $url);
        curl_setopt($con, CURLOPT_HEADER, 0);
        curl_setopt($con, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($con, CURLOPT_TIMEOUT, 60);
        $result = curl_exec($con);
        curl_close($con);
        return $result;
    }
}