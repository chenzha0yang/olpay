<?php
namespace App\Http\PayModels\Online;

use App\ApiModel;

class Rongcaipay extends ApiModel
{
    public static $method = 'post'; //提交方式 必加属性 post or get

    public static $reqType = 'form'; //提交类型 必加属性 form or curl or fileGet

    public static $payWay = 0; //是否需要生成二维码 必加属性 2 3 4 5

    public static $resType = 'json'; //curl file_get_contents 返回的数据类型json/xml/str

    public static $unit = 1; //金额单位  默认1为元  2:单位为分

    public static $postType = false; //数据提交类型 默认false 一维数组 or json/str/多维数组

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
        $ServerUrl = $reqData['ServerUrl'];// 异步通知地址
        $returnUrl = $reqData['returnUrl'];// 同步通知地址

        //TODO: do something
        $data = [];
        $data['requestNo'] = $order;                //请求流水号
        $data['productId'] = $bank;                 //产品类型
        $data['transId'] = '10';                    //由平台统一提供）
        $data['merNo'] = $payConf['business_num'];  //商户好
        $data['orderDate'] = date("Ymd", time());   //时间
        $data['orderNo'] = $order;                  //商户订单号
        $data['returnUrl'] = $returnUrl;            //同步地址
        $data['notifyUrl'] = $ServerUrl;            //异步地址
        $data['transAmt'] = $amount * 100;        //金额
        $data['commodityName'] = 'beizhu';          //商品名称
        $data['memo'] = 'beizhu';                   //备注
        $signStr = self::getSignStr($data, false ,true);
        $data['signature'] = strtoupper(self::getMd5Sign("{$signStr}", $payConf['md5_private_key']));
		$data['version'] = "V4.0";                  //版本
		$data['cashier'] = 1;                       //0 直连 、 1展示收银台 不输入默认无收银台
        unset($reqData);
        return $data;
    }
}