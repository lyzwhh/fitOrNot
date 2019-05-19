<?php
/**
 * Created by PhpStorm.
 * User: yuse
 * Date: 19/5/17
 * Time: 下午5:43
 */
namespace App\Services;
Class WxxcxService
{
    private $CODE2SESSION_URL = "https://api.weixin.qq.com/sns/jscode2session?appid=%s&secret=%s&js_code=%s&grant_type=authorization_code";


    public function login($js_code)
    {
        $code2session_url = sprintf($this->CODE2SESSION_URL,env('WX_APP_ID'),env('WX_APP_SECRET'),$js_code);
        $userInfo = $this->httpRequest($code2session_url);
        if(!isset($userInfo['session_key'])){
            return $userInfo;
        }
        $userInfo['errcode'] = 0;
        return $userInfo;
    }


    private function httpRequest($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        if($output === FALSE ){
            return false;
        }
        curl_close($curl);
        return json_decode($output,JSON_UNESCAPED_UNICODE);
    }
}