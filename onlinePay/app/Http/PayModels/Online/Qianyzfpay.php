<?php

namespace App\Http\PayModels\Online;

use App\ApiModel;

class Qianyzfpay extends ApiModel
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
	    if ($payConf['is_app'] == 1) {
		    self::$isAPP = true;
	    }

        //TODO: do something
        self::$reqType = 'curl';
        self::$payWay  = $payConf['pay_way'];
        self::$resType = 'other';
        self::$httpBuildQuery = true;

        $data['userid'] = $payConf['business_num'];//商户号
        $data['type'] = $bank;//银行编码
        $data['innerorderid'] = $order;//订单号
        $data['money'] = $amount;//订单金额
        $data['notifyurl'] = $ServerUrl;
        $signStr =  self::getSignStr($data, true,true);
        $data['sign'] = strtoupper(self::getMd5Sign("{$signStr}&key=", $payConf['md5_private_key'])); //MD5签名
	    
        unset($reqData);
        return $data;
    }

    /**
     * @param $response
     * @return mixed
     */
    public static function getQrCode($response)
    {
        $result = json_decode($response, true);
        if ($result['code'] == 'success') {
            if ($result['type'] == 1) {
	            $res['codeUrl'] = $result['data'];
            }elseif($result['type'] == 2){
                echo $result['data'];exit;
            } else {
                $res['payUrl'] = $result['data'];
            }
        } else {
        	$res['code'] = $result['code'];
	        $res['msg'] = $result['msg'];
        }
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
        $sign = $data['sign'];
        unset($data['sign']);
        $signStr =  self::getSignStr($data, true,true);
        $signTrue = strtoupper(self::getMd5Sign("{$signStr}&key=", $payConf['md5_private_key'])); //MD5签名
        if ($sign == $signTrue && $data['status'] == '2') {
            return true;
        } else {
            return false;
        }
    }

}