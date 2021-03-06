<?php

namespace App\Http\PayModels\Online;

use App\ApiModel;

class Chenxinfpay extends ApiModel
{
    public static $method = 'post'; //提交方式 必加属性 post or get

    public static $reqType = 'form'; //提交类型 必加属性 form or curl or fileGet

    public static $payWay = 0; //是否需要生成二维码 必加属性 2 3 4 5

    public static $resType = 'json'; //curl file_get_contents 返回的数据类型json/xml/str

    public static $unit = 1; //金额单位  默认1为元  2:单位为分

    public static $postType = false; //数据提交类型 默认false 一维数组 or json/str/多维数组  getRequestByType

    public static $httpBuildQuery = false; //默认false/true为curl提交参数需要http_build_query

    public static $isAPP = false; // 判断是否跳转APP 默认false

    public static $pcWap = 2;

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

        //TODO: do something
        $data['appid']        = $payConf['business_num']; //商户号
        $data['pay_type']     = $bank; //支付方式
        $data['amount']       = sprintf('%.2f', $amount); //金额 元
        $data['out_trade_no'] = $order;
        $data['version']      = 'v1.1';
        $data['callback_url'] = $ServerUrl;
        $data['success_url']  = $returnUrl;
        $data['error_url']    = $returnUrl;

        $data['out_uid']      = time();
        $signStr            = self::getSignStr($data, true, true);
        $data['sign']       = strtoupper(self::getMd5Sign("{$signStr}&key=", $payConf['md5_private_key']));
        unset($reqData);
        return $data;
    }

    //回调处理
    public static function SignOther($type, $data, $payConf)
    {
        $sign = $data['sign'];
        unset($data['sign']);
        $signStr = self::getSignStr($data, true, true);
        $mySign  = strtoupper(self::getMd5Sign("{$signStr}&key=", $payConf['md5_private_key']));
        if ($mySign == $sign && $data['callbacks'] == 'CODE_SUCCESS') {
            return true;
        } else {
            return false;
        }
    }
}
