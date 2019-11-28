<?php
namespace App\Http\PayModels\Online;

use App\ApiModel;
use App\Http\Extensions\Curl;

class Tiemapay extends ApiModel
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

        self::$reqType = 'curl';
        self::$payWay = $payConf['pay_way'];
        self::$method = 'header';
        self::$unit = 2;

        $data['service'] = $bank;  //ID
        $data['mch_id'] = $payConf['business_num'];  //ID
        $data['random'] =rand(111111111,99999999999) ; //随机字符串
        $data['trade_no'] = $order;  //平台生成的订单号
        $data['body'] = 'miaoshu'; //描述
        $data['total_fee'] = $amount *100;
        $data['return_url'] = $ServerUrl;
        $data['notify_url'] = $ServerUrl;
        $data['attach'] ='fujiaxinxi' ;
        $signStr  = self::getSignStr($data, true, true);
        $data['sign'] = strtoupper(md5(md5($signStr . '&key=' . $payConf['md5_private_key'])));

        $header = array(
            'Content-Type: application/json; charset=utf-8',
        );
        $post['data'] = json_encode($data);
        $post['httpHeaders'] = $header;
        $post['trade_no'] = $order;
        $post['total_fee'] = $amount;
        unset($reqData);
        return $post;
    }

    public static function getVerifyResult($request, $mod)
    {
        $arr = $request->all();
        if (isset($arr['total_fee'])) {
            $arr['total_fee'] = $arr['total_fee'] / 100;
        }
        return $arr;
    }


    /**
     * 回掉特殊处理
     * @param $model
     * @param $data - 返回的数据 - array
     * @param $payConf
     * @return bool
     */
    public static function SignOther($model, $data, $payConf)
    {
        $sign =strtoupper($data['sign']) ;
        unset($data['sign']);
        $signStr  = self::getSignStr($data, true, true);
        $mySign = strtoupper(md5(md5($signStr . '&key=' . $payConf['md5_private_key'])));

        if($sign == $mySign ){
            return true;
        }else{
            return false;
        }
    }

}