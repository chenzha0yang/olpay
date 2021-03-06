<?php

namespace App\Http\PayModels\Online;

use App\ApiModel;

class Fengchaopay extends ApiModel
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
     * @param array       $reqData 接口传递的参数
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

        self::$isAPP = true;
        self::$reqType = 'curl';
        self::$payWay  = $payConf['pay_way'];

        //TODO: do something
        $data = array(
            'payMethod'   => $bank,
            'order_user'  => $order,
            'money_order' => $amount,
            'shop_id'     => $payConf['business_num'],
            'notify_url'  => $ServerUrl,
            'ip'          => request()->ip(),
        );
        $data['sign'] = md5($data['shop_id'] . $data['order_user'] . $data['money_order'] . $data['notify_url'] . $payConf['md5_private_key']);
        unset($reqData);
        return $data;
    }

    /**
     * 回调处理
     * @param $mod
     * @param $data
     * @param $payConf
     * @return bool
     */
    public static function SignOther($mod,$data,$payConf){
        $mysign = md5($data['fxstatus'].$data['fxid'].$data['fxddh'].$data['fxfee'].$payConf['md5_private_key']);
        if($mysign == $data['fxsign'] && $data['fxstatus'] == 1){
            return true;
        }else{
            return false;
        }
    }

}