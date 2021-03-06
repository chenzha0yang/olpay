<?php
namespace App\Http\PayModels\Online;

use App\ApiModel;

class Yawupay extends ApiModel
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

        //TODO: do something
        self::$method = 'get';
        $data['id'] = $payConf['business_num'];//商户号
        $data['pay_id'] = 'qwer132';//用户唯一标识 用户名或者用户ID
        $data['price'] = $amount;
        $data['order_no'] = $order;//订单
        $data['param'] = $order;//商品描述
        $data['timestamp'] = time();
        ksort($data);
        $signStr = self::getSignStr($data, true, true);
        $signStr = substr($signStr,0,-1);
        $data['sign']= self::getMd5Sign("{$signStr}", $payConf['md5_private_key']);
        $data['act'] = 'Yawupay';
        unset($reqData);
        return $data;
    }   
    //订单查询
    public function orderNumResult($order, $amount, $payConf){

        $url = "163.8a6.cn/api/order/getOrder";
        $data = array(
            "page" => '1',
            "id" => $payconf['pay_id'],    //商户id
            "order_no" => $order,      //订单号
            "limit" => '20',             //订单数
        );
        ksort($data);
        $signStr = "";
        foreach($data as $key=>$value){
            if(!empty($value)){
                $signStr .= $key."=".$value."&";
            }   
        }
        $signStr = substr($signStr,0,-1);
        $data['sign'] = md5($signStr.$payconf['pay_key']);//签名
        $resp = $this->httpGet($url,$data);
        $res = json_decode($resp,true);
        if($res['code'] == "1" && $res['msg'] == 'success'){
            return true;
        } else {
            $this->add_callback($order,$resp,'');// 订单结果失败 加日志
            return false;
        }
    }
}