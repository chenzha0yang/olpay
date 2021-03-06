<?php

namespace App\Http\PayModels\Online;

use App\ApiModel;

class Kuaifupingpay extends ApiModel
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

        self::$reqType = 'curl';
        self::$payWay  = $payConf['pay_way'];
        self::$resType = 'other';
        //判断是否需要跳转链接 is_app=1开启 2-关闭
        if ($payConf['is_app'] == 1) {
            self::$isAPP = true;
        }
        $data = array(
            'Merid'         => $payConf['business_num'],   //商户号
            'Merorderno'    => $order,
            'Toamount'      => sprintf('%.2f',$amount),
            'Return_url'    => $returnUrl,
            'Paymentype'    => $bank,
            'Notifyurl'     => $ServerUrl,
        );
        $signStr      = self::getSignStr($data, true,true);
        $data['Sign'] = self::getMd5Sign("{$signStr}&key=", $payConf['md5_private_key']); //MD5签名
        if($payConf['pay_way'] == 1){   //网银
            $data['Paymentype'] = '105';
            $data['bankCode']   = $bank;
        }

        unset($reqData);
        return $data;
    }

    /**
     * html返回值处理
     * @param $response
     */
    public static function getQrCode($response){
        $responseData = json_decode($response,true);
        print_r($responseData['result']);
    }

}