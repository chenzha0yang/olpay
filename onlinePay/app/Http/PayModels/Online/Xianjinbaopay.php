<?php
/**
 * Created by PhpStorm.
 * User: JS-00036
 * Date: 2018/9/14
 * Time: 14:56
 */

namespace App\Http\PayModels\Online;

use App\ApiModel;

class Xianjinbaopay extends ApiModel
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
        $order = $reqData['order'];  //订单号
        $amount = $reqData['amount'];  //金额
        $bank = $reqData['bank'];
        $ServerUrl = $reqData['ServerUrl'];// 异步通知地址
        $returnUrl = $reqData['returnUrl'];// 同步通知地址

        self::$unit = 2;
        self::$reqType = 'curl';
        self::$resType = 'other';
        self::$postType = true;
        self::$payWay = $payConf['pay_way'];
        self::$httpBuildQuery = true;

        $data = [
            'version'           => 'V1.0.0',                 //版本号
            'mch_id'            => $payConf['business_num'], //商户号
            'nonce_str'         => self::randStr(32),   //随机字符串
            'body'              => 'VIP',                   //商品描述
            'out_trade_no'      => $order,              //订单号
            'total_fee'         => $amount*100,         //订单价格
            'spbill_create_ip'  => '127.0.0.1',
            'notify_url'        => $ServerUrl,      //异步通知地址
            'trade_type'        => $bank,           //支付类型
        ];
        if($payConf['is_app'] == 1){ //H5
            self::$isAPP = true;
            $data['return_url'] = $returnUrl;
        }
        if($payConf['pay_way'] == '9'){ //银联快捷
            $data['cardNo'] = $payConf['message1'];
        }
        $signStr = self::getSignStr($data, true, true); //排序拼接字符串
        $data['sign'] = strtoupper(self::getMd5Sign("{$signStr}"."&key=", $payConf['md5_private_key'])); //加密
        //将数组转换成xml
        $xml = "<xml>";
        foreach ($data as $key => $val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        //将xml转换成数组
        $post = [];
        $post['xml'] = $xml;
        $post['out_trade_no'] = $data['out_trade_no'];
        $post['total_fee'] = $data['total_fee'];

        return $post;
    }

    /**
     * 提交数据
     * @param $data
     * @return mixed
     */
    public static function getRequestByType($data)
    {
        return $data['xml'];
    }

    /**
     * 二维码处理
     * @param $response
     * @return mixed
     */
    public static function getQrCode($response)
    {
        libxml_disable_entity_loader(true);
        $result = json_decode(json_encode(simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        if($result['result_code'] == "SUCCESS" && $result['return_code'] == 'SUCCESS'){
            if($result['trade_type'] == 'trade.weixin.h5pay' || $result['trade_type'] == 'trade.alipay.h5pay'){
                $data['prepay_url'] = $result['prepay_url'];
            }else{
                $data['code_url']   = $result['code_url'];
            }
        } else{
            $data['return_code'] = $result['return_code'];
            $data['return_msg']  = $result['return_msg'];
        }
        return $data;
    }

}