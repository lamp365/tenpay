# tenpay
个人封装的一个composer组件，财付通支付组件
以及对应的QQ扫码支付组件
#使用如下  
include './vendor/autoload.php';
//财付通
$tenpay_config = array(  
    'partner' => '1486474181',  
    'key' => '4ff7a938fc9489b17f7b9b4f5127c59a',  
    'return_url' => 'http://baidu.com',  
    'notify_url' => 'http://baidu.com',  
);  
$pay = new kevin365\tenpay\Tenpay($tenpay_config);  
$data['ordersn'] = date('YmdHis').uniqid();  
$data['title']   = '测试商品';  
$data['price']   = '1';  
$pay->pay($data);  

//QQ扫码   
//入参   
$params = array();   
$params["out_trade_no"] = "20160512161914".uniqid();    
$params["sub_mch_id"] = "";   
$params["body"] = "body_test_中文";   
$params["device_info"] = "WP00000001";   
$params["fee_type"] = "CNY";   
$params["notify_url"] = "https://10.222.146.71:80/success.xml";   
$params["spbill_create_ip"] = "127.0.0.1";   
$params["total_fee"] = "1";  
$params["trade_type"] = "NATIVE";   
   
//参数检测   
//实际业务中请校验参数，本demo略   
//  
$pay_config = array(   
    'MCH_ID'         => '1486419881',   //QQ钱包商户号   
    'SUB_MCH_ID'     => '',   //子账户号   
    'MCH_KEY'        => '21212',   //api密钥   
    'CERT_FILE_PATH' => '',   //证书私钥   
    'KEY_FILE_PATH'  => '',  //证书公钥   
    'NOTIFY_URL'     => '',   //成功回调地址   
);   
  
//api调用   
$qpayApi = new \kevin365\tenpay\qpay\QpayMchAPI('https://qpay.qq.com/cgi-bin/pay/qpay_unified_order.cgi', null, 10,$pay_config);   
$ret = $qpayApi->reqQpay($params);   
$Qpay = new \kevin365\tenpay\qpay\QpayMchUtil();   
print_r($Qpay->xmlToArray($ret));  
//最后得到code_url生成二维码，用手机扫码可完成支付   
#composer地址  
https://packagist.org/packages/kevin365/tenpay    
