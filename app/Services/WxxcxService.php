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
    private $ACCESSTOKEN_URL = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s";

    private $redisService;

    public function __construct(RedisService $redisService)
    {
        $this->redisService = $redisService;
    }

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

    /**
     * 被getAccessToken() 调用
     *
     * 返回
     *      access_token	string	获取到的凭证
            expires_in	    number	凭证有效时间，单位：秒。目前是7200秒之内的值。
            errcode	        number	错误码
            errmsg	        string	错误信息
     * @return bool|mixed
     */
    public function receiveAccessToken()
    {
        $accessToken_url = sprintf($this->ACCESSTOKEN_URL,env('WX_APP_ID'),env('WX_APP_SECRET'));
        $accessTokenInfo = $this->httpRequest($accessToken_url);
        if(!isset($accessTokenInfo['access_token'])){
            return $accessTokenInfo;
        }
        $accessTokenInfo['errcode'] = 0;
        return $accessTokenInfo;
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



    /**
     *
     * 如果Redis 中保存着accessToken,直接返回
     * 如果没有,调用获取函数然后保存,再返回
     *
     * @return mixed
     */
    public function getAccessToken()
    {
        if($this->redisService->checkCache('accessToken'))
        {
            return $this->redisService->getCache('accessToken');
        }
        else
        {
            $accessTokenInfo = $this->receiveAccessToken();
            if(!isset($accessTokenInfo['access_token'])){
                return -1;                      //TODO:: 接下这个-1
            }
            $this->redisService->setAccessToken($accessTokenInfo['access_token']);
            return $accessTokenInfo['access_token'];

        }
    }


}