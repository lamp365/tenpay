<?php
namespace kevin365\tenpay\qpay;
/**
 * qpayMachAPI.php 业务调用方可做二次封装
 * Created by HelloWorld
 * vers: v1.0.0
 * User: Tencent.com
 */
class QpayMchAPI{
    protected $url;
    protected $isSSL;
    protected $timeout;

    public $pay_config = array(
        'MCH_ID'         => '',   //QQ钱包商户号
        'SUB_MCH_ID'     => '',   //子账户号
        'MCH_KEY'        => '',   //api密钥
        'CERT_FILE_PATH' => '',   //证书私钥
        'KEY_FILE_PATH'  => '',  //证书公钥
        'NOTIFY_URL'     => '',   //成功回调地址
    );

    /**
     * QpayMchAPI constructor.
     *
     * @param       $url       接口url
     * @param       $isSSL     是否使用证书发送请求
     * @param int   $timeout   超时
     */
    public function __construct($url, $isSSL, $timeout = 5,$pay_config){
        $this->url = $url;
        $this->isSSL = $isSSL;
        $this->timeout = $timeout;

        $this->pay_config['MCH_ID']         = $pay_config['MCH_ID'];
        $this->pay_config['SUB_MCH_ID']     = $pay_config['SUB_MCH_ID'];
        $this->pay_config['MCH_KEY']        = $pay_config['MCH_KEY'];
        $this->pay_config['CERT_FILE_PATH'] = $pay_config['CERT_FILE_PATH'];
        $this->pay_config['KEY_FILE_PATH']  = $pay_config['KEY_FILE_PATH'];
        $this->pay_config['NOTIFY_URL']     = $pay_config['NOTIFY_URL'];
    }

    public function reqQpay($params){
        $ret = array();
        //商户号
        $params["mch_id"] = $this->pay_config['MCH_ID'];

        $QpayMch = new QpayMchUtil();
        //随机字符串
        $params["nonce_str"] = $QpayMch->createNoncestr();
        //签名
        $params["sign"] = $QpayMch->getSign($params,$this->pay_config['MCH_KEY']);
        //生成xml
        $xml = $QpayMch->arrayToXml($params);

        if(isset($this->isSSL)){
            $ret =  $QpayMch->reqByCurlSSLPost($xml, $this->url, $this->timeout);
        }else{
            $ret =  $QpayMch->reqByCurlNormalPost($xml, $this->url, $this->timeout);
        }
        return $ret;
    }

}