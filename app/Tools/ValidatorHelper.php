<?php
/**
 * Created by PhpStorm.
 * User: yuse
 * Date: 19/9/16
 * Time: 下午3:44
 */

namespace App\Tools;

use Illuminate\Support\Facades\Validator;

class ValidatorHelper
{
    /**
     * 对一个数组进行表单验证 , 错误在validator->fails()中 , 需要主动判断 , 不会报错
     * @param array $inputs
     * @param array $rules
     * @return mixed
     */
    public static function validateCheck(array $inputs,array $rules)
    {
        $validator = Validator::make($inputs,$rules);

        return $validator;
    }

    /**
     * 过滤掉恶意用户的多余参数 , 返回只存在rules key中的key元组
     * @param array $inputData
     * @param array $rules
     * @return array
     */
    public static function getInputData(array $inputData,array $rules)
    {
        $setData = [];

        foreach ($rules as $key => $rule)
        {
            if (isset($inputData[$key]))
            {
                $setData[$key] = $inputData[$key];
            }
        }


        return $setData;
    }

    public static function checkAndGet(array $inputData,array $rules)
    {
        $validator = Validator::make($inputData,$rules);

        if ($validator->fails()) {
            return response()->json([
                'errmsg' => $validator->errors(),
                'errcode'   =>  -1
            ]);
        }

        $setData = [];

        foreach ($rules as $key => $rule)   // setData 中不存在(而不是一条null)inputData里没有的key , 方便update和insert
        {
            if (isset($inputData[$key]))
            {
                $setData[$key] = $inputData[$key];
            }
        }


        return $setData;
    }
}