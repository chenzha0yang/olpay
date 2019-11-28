<?php

namespace App\Http\PayModels\Online;

use App\ApiModel;
use Illuminate\Http\Request;

class Juzijinfupay extends ApiModel
{
    public static $method = 'post'; //提交方式 必加属性 post or get

    public static $reqType = 'form'; //提交类型 必加属性 form or curl or fileGet

    public static $payWay = 0; //是否需要生成二维码 必加属性 2 3 4 5

    public static $resType = 'json'; //curl file_get_contents 返回的数据类型json/xml/str

    public static $unit = 1; //金额单位  默认1为元  2:单位为分

    public static $httpBuildQuery = false; //默认 false  true为curl提交参数 需要http_build_query

    public static $postType = false; //数据提交类型 默认false 一维数组   or  json ／str ／多维数组

    public static $isAPP = false; // 判断是否跳转APP 默认false

    /**
     * @param array $reqData 接口传递的参数
     * @param array $payConf
     * @return array
     */
    public static function getAllInfo($reqData, $payConf)
    {
        /**
         * 参数赋值，方法间传递数组
         */
        $order     = $reqData['order'];
        $amount    = $reqData['amount'];
        $bank      = $reqData['bank'];
        $ServerUrl = $reqData['ServerUrl'];// 异步通知地址
        $returnUrl = $reqData['returnUrl'];// 同步通知地址

        self::$method = 'get';

        $data['merchant_id'] = $payConf['business_num'];
        $data['order_id']    = $order;
        $data['amount']      = $amount;
        $data['notify_url']  = $ServerUrl;
        $data['return_url']  = $returnUrl;
        $data['pay_method']  = $bank;
        $signStr             = "merchant_id={$data['merchant_id']}&order_id={$order}&amount={$amount}";
        $sign                = strtolower(self::getMd5Sign("{$signStr}&sign=", $payConf['md5_private_key'])); //MD5签名
        $data['sign']        = $sign;

        unset($reqData);
        return $data;
    }

    public static function getVerifyResult(Request $request, $mod)
    {
        $arr = $request->getContent();
        $res =  json_decode($arr,true);
        return $res;
    }

    /**
     * @param $type
     * @param $json
     * @param $payConf
     * @return bool
     */
    public static function SignOther($type, $data, $payConf)
    {
        $signStr             = "merchant_id={$payConf['business_num']}&order_id={$data['order_id']}&amount={$data['amount']}";
        $sign                = strtolower(self::getMd5Sign("{$signStr}&sign=", $payConf['md5_private_key'])); //MD5签名
        if ($sign == strtolower($data['sign'])) {
            return true;
        } else {
            return false;
        }
    }


}