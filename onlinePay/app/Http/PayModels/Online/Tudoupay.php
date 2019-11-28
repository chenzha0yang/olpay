<?php

namespace App\Http\PayModels\Online;

use App\ApiModel;

class Tudoupay extends ApiModel
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
        $type = '1';
        if ($payConf['is_pc_wap'] == '2') {
            $type = '2';
        }
        $data['merchantCode'] = $payConf['business_num'];//商家号
        if ($payConf['pay_way'] == '1') {
            $data['openType']  = $type;//打开类型
            $data['returnUrl'] = $returnUrl;
            $data['userType']  = '1';
            $data['cardType']  = '1';
            $data['bankCode']  = $bank;
        } else {
            $data['serviceType'] = $bank;//业务类型
        }
        $data['notifyUrl']        = $ServerUrl;
        $data['interfaceVersion'] = '1.0';
        $data['clientIp']         = '127.0.0.1';
        $data['orderId']          = $order;
        $data['amount']           = $amount;
        $data['productName']      = 'productName';
        $data['productDesc']      = 'desc';
        $data['productExt']       = 'ext';
        $signStr                  = self::getSignStr($data, false, true);
        $data['sign']             = strtoupper(self::getMd5Sign("{$signStr}", $payConf['md5_private_key']));
        $data['payway']           = $payConf['pay_way'];
        $data['pcwap']            = $payConf['is_pc_wap'];

        unset($reqData);
        return $data;
    }
}