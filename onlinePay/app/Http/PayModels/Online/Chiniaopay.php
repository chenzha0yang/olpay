<?php

namespace App\Http\PayModels\Online;

use App\ApiModel;

class Chiniaopay extends ApiModel
{
    public static $method = 'post'; //提交方式 必加属性 post or get

    public static $reqType = 'form'; //提交类型 必加属性 form or curl or fileGet

    public static $payWay = 0; //是否需要生成二维码 必加属性 2 3 4 5

    public static $resType = ''; //curl file_get_contents 返回的数据类型json/xml/str

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

        $data = [];
        $data['version']    = '1.0';//版本号
        $data['customerid'] = $payConf['business_num'];//商户编号
        $data['total_fee']  = number_format($amount, 2, '.', '');//订单金额
        $data['sdorderno']  = $order;//订单编号
        $data['notifyurl']  = $ServerUrl;//异步通知地址
        $data['returnurl']  = $returnUrl;//同步地址
        $signStr            = self::getSignStr($data, false);
        $data['sign']       = self::getMd5Sign("{$signStr}", $payConf['md5_private_key']);

        if ($payConf['pay_way'] == '1') {
            $data['paytype']  = 'bank';//支付类型
            $data['bankcode'] = $bank;//网银直连不可为空，其他支付方式可为空
        } else {
            $data['paytype']  = $bank;//支付类型
            $data['bankcode'] = '';//网银直连不可为空，其他支付方式可为空
        }
        $data['remark']   = '';
        $data['get_code'] = '';

        unset($reqData);
        return $data;
    }
}