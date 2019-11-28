<?php

namespace App\Http\PayModels\Online;

use App\ApiModel;

class Feimaipay extends ApiModel
{
    public static $method = 'post'; //提交方式 必加属性 post or get

    public static $reqType = 'form'; //提交类型 必加属性 form or curl or fileGet

    public static $payWay = 0; //是否需要生成二维码 必加属性 2 3 4 5

    public static $resType = 'json'; //curl file_get_contents 返回的数据类型json/xml/str

    public static $unit = 1; //金额单位  默认1为元  2:单位为分

    public static $postType = false; //数据提交类型 默认false 一维数组 or json/str/多维数组  getRequestByType

    public static $httpBuildQuery = false; //默认false/true为curl提交参数需要http_build_query

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
        $ServerUrl = $reqData['ServerUrl']; // 异步通知地址
        $returnUrl = $reqData['returnUrl']; // 同步通知地址

        self::$isAPP = true;

        //TODO: do something
        self::$unit    = 2; // 单位 ： 分
        self::$reqType = 'curl';
        self::$payWay  = $payConf['pay_way'];
        self::$method  = 'header';
        self::$resType = 'other';

        $data['mchId']         = $payConf['business_num'];
        $data['transactionId'] = $order; //订单号
        $data['amount']        = $amount * 100; //金额
        $data['channel']       = $bank; //支付渠道
        $data['memo']          = 'MateX';
        $data['callbackUrl']   = $ServerUrl; //异步通知地址
        $data['ip']            = self::getClientIP();
        $signStr               = self::getSignStr($data, true, true);
        $data['sign']          = strtoupper(md5("{$payConf['md5_private_key']}&{$signStr}"));

        $json                  = json_encode($data, JSON_UNESCAPED_SLASHES);
        $post['data']          = $json;
        $post['httpHeaders']   = array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($json)
        );
        $post['transactionId'] = $data['transactionId'];
        $post['amount']        = $data['amount'];

        unset($reqData);
        return $post;
    }

    public static function getVerifyResult($request, $mod)
    {
        $arr = $request->all();
        $arr['amount'] = $arr['amount'] / 100;
        return $arr;
    }


    public static function getQrCode($response)
    {
        $arr               = json_decode($response, true);
        $arr['orderUrl']  = $arr['urls']['orderUrl'];
        return $arr;
    }

    public static function SignOther($model, $data, $payConf)
    {
        $sign = $data['sign'];
        unset($data['sign']);
        $signStr = self::getSignStr($data, true, true);
        $mySign  = md5("{$payConf['md5_private_key']}&{$signStr}");
        if ($sign == $mySign) {
            return true;
        }
        return false;

    }

    /**
     * @return array|false|string
     * 获取客户端真实IP
     */
    public static function getClientIP()
    {
        global $ip;
        if (getenv("HTTP_CLIENT_IP"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if (getenv("HTTP_X_FORWARDED_FOR"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if (getenv("REMOTE_ADDR"))
            $ip = getenv("REMOTE_ADDR");
        else $ip = "Unknow";
        return $ip;
    }
}