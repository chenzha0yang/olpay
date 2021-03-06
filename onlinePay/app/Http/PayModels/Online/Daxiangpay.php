<?php

namespace App\Http\PayModels\Online;

use App\ApiModel;

class Daxiangpay extends ApiModel
{
    public static $method = 'post'; //提交方式 必加属性 post or get

    public static $reqType = 'form'; //提交类型 必加属性 form or curl or fileGet

    public static $payWay = 0; //是否需要生成二维码 必加属性 2 3 4 5

    public static $resType = 'json'; //curl file_get_contents 返回的数据类型json/xml/str

    public static $unit = 1; //金额单位  默认1为元  2:单位为分

    public static $postType = false; //数据提交类型 默认false 一维数组 or json/str/多维数组  getRequestByType

    public static $httpBuildQuery = false; //默认false/true为curl提交参数需要http_build_query

    public static $isAPP = false; // 判断是否跳转APP 默认false

    public static $changeUrl = true;

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


        $data['account_id']     = $payConf['business_num'];//商户号
        $data['thoroughfare']         = $bank;
        $data['out_trade_no']          = $order;
        $data['amount']     = sprintf('%.2f',$amount);
        $data['callback_url']   = $ServerUrl;
        $data['success_url']   = $ServerUrl;
        $data['error_url']   = $returnUrl;
        $data['content_type']   = 'web';
        $data['robin']   = '1';
        $data['sign'] = self::isSign($payConf['md5_private_key'],$data);
        $post['data']  = $data;
        $post['queryUrl'] = $reqData['formUrl'].'/api/Gateway/create';
        $post['out_trade_no'] = $data['out_trade_no'];
        $post['amount'] = $data['amount'];
        unset($reqData);
        return $post;
    }

    public static function isSign($key_id, $array)
    {
        $data = md5($array['amount'].$array['out_trade_no']);
        return md5(strtolower($key_id).$data);
    }


    public static function getQrCode($response)
    {
        $data = json_decode($response, true);
        if ($data['code'] == '200') {
            $data['payUrl'] = $data['data']['jump_url'];
        }
        return $data;
    }

    public static function SignOther($type, $data, $payConf)
    {
        $sign = strtolower($data['sign']);
        unset($data['sign']);
        $signTrue = self::isSign($payConf['md5_private_key'],$data);
        if ($sign == $signTrue && $data['status'] == 'success') {
            return true;
        }
        return false;
    }


}