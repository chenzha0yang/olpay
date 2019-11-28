<?php

namespace App\Http\PayModels\Online;

use App\ApiModel;

class Mypay extends ApiModel
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

        $data               = [];
        $data['merId'] = $payConf['business_num'];//商户号
        $data['payType'] = $bank;//支付类型
        $data['merchantNo'] = $order;
        $data['terminalClient'] = 'pc';//支付装置
        if($payConf['is_app'] == '1'){
            $data['terminalClient'] = 'wap';//支付装置
        }
        $data['tradeDate'] = date("YmdHis");
        $data['amount'] = sprintf("%.2f",$amount);
        $data['clientIp'] = $_SERVER['REMOTE_ADDR'];
        $data['notifyUrl'] = $ServerUrl;
        $signStr            = self::getSignStr($data, true, true);
        $data['sign']       = strtoupper(self::getMd5Sign("{$signStr}&key=", $payConf['md5_private_key']));
        $data['version'] = 'V1.0';//接口版本
        $data['orgId'] = $payConf['public_key'];//机构号
        $data['extra'] = $order;//回传字段
        $data['signType'] = 'MD5';

        unset($reqData);
        return $data;
    }
	
	
	public static function SignOther($type, $data,$payConf)
	{
		$mySign = $data['sign'];
		unset($data['sign']);
		unset($data['extra']);
		unset($data['clientIp']);
		$signStr            = self::getSignStr($data, true, true);
        $sign = strtoupper(md5($signStr . '&key=' . $payConf['md5_private_key']));
        if ($sign == $mySign) {
			return true;
		} else {
			return false;
		}
	}

}