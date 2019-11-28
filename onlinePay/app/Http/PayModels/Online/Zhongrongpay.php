<?php

namespace App\Http\PayModels\Online;

use App\ApiModel;

class Zhongrongpay extends ApiModel
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

        //TODO: do something
        $data = [];
        if ((int)$payConf['pay_way'] === 1) { // 网银
            $type                   = 'bank_type';
            $data['bank_card_type'] = '0';
        } else {
            $type = $bank;
        }
        $data['pay_type']     = $type;  // 交易类型
        $data['version']      = '3.2';  // 版本号
        $data['mch_id']       = $payConf['business_num'];  // 商户号
        $data['out_trade_no'] = $order;  // 商户订单号
        $data['total_fee']    = sprintf("%.2f", $amount);  // 支付金额
        $data['callback_url'] = $ServerUrl;  // 异步通知地址Url
        $signStr              = "callback_url={$data['callback_url']}&mch_id={$data['mch_id']}&out_trade_no={$data['out_trade_no']}&pay_type={$data['pay_type']}&total_fee={$data['total_fee']}&version={$data['version']}";
        $sign                 = self::getMd5Sign("{$signStr}&key=", $payConf['md5_private_key']);
        $data['return_url']   = $returnUrl;  // 同步跳转地址Url
        if ((int)$payConf['pay_way'] === 1) { // 网银
            $data['bank_card_type'] = $bank;
        }
        $data['code_type'] = '';  // 返回数据格式  跳转到扫码支付页面/返回二维码，“空”：跳转到扫码支付页面，“Y”：返回二维码url。默认：空
        $data['body']      = '';  // 商品描述
        $data['attach']    = '';  // 附加字符串
        $data['sign']      = $sign;  // 签名
        unset($reqData);
        return $data;
    }



    //回调处理
    public static function SignOther($type, $data, $payConf)
    {
        $sign    = strtoupper($data['sign']);
        $signStr = "mch_id={$data['mch_id']}&out_trade_no={$data['out_trade_no']}&pay_type={$data['pay_type']}&retCode={$data['retCode']}&sdpayno={$data['sdpayno']}&total_fee={$data['total_fee']}";
        $mySign  = strtoupper(self::getMd5Sign("{$signStr}&key=", $payConf['md5_private_key']));
        if ($mySign == $sign) {
            return true;
        } else {
            return false;
        }
    }
}