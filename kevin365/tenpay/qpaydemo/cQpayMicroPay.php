<?php
/**
 * cQpayMicroPay.php
 * Created by by HelloWorld
 * vers: v1.0.0
 * User: Tencent.com
 */

require_once (dirname(__DIR__) . '/qpay/QpayMchAPI.php');

//入参
$params = array();
$params["out_trade_no"] = "20160512161914_BBC" . "A";
$params["sub_mch_id"] = "1900005911";
$params["body"] = "body_test_中文";
$params["device_info"] = "WP00000001";
$params["fee_type"] = "CNY";
$params["notify_url"] = "https://10.222.146.71:80/success.xml";
$params["spbill_create_ip"] = "127.0.0.1";
$params["total_fee"] = "1";
$params["trade_type"] = "MICROPAY";
$params["auth_code"] = "910728310408849937";

//参数检测
//实际业务中请校验参数，本demo略
//
$pay_config = array(
    'MCH_ID'         => '',   //QQ钱包商户号
    'SUB_MCH_ID'     => '',   //子账户号
    'MCH_KEY'        => '',   //api密钥
    'CERT_FILE_PATH' => '',   //证书私钥
    'KEY_FILE_PATH'  => '',  //证书公钥
    'NOTIFY_URL'     => '',   //成功回调地址
);
//api调用
$qpayApi = new QpayMchAPI('https://qpay.qq.com/cgi-bin/pay/qpay_micro_pay.cgi', null, 10,$pay_config);
$ret = $qpayApi->reqQpay($params);

print_r(QpayMchUtil::xmlToArray($ret));