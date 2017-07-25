<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 2017/7/25
 * Time: 18:32
 */
namespace kevin\tenpay;

class Tenpay{

    public $tenpay_config = array(
        'partner'    => '**********',          //这里是你在成功申请财付通接口后获取到的商户号；
        'key'        => '*******************', //这里是你在成功申请财付通接口后获取到的密钥
        'return_url' => '********/tenreturnurl',
        'notify_url' => '********/tennotifyurl',
    );
    public function __construct($tenpay_config)
    {
        $this->tenpay_config['partner']    = $tenpay_config['partner'];
        $this->tenpay_config['key']        = $tenpay_config['key'];
        $this->tenpay_config['return_url'] = $tenpay_config['return_url'];
        $this->tenpay_config['notify_url'] = $tenpay_config['notify_url'];
    }

    public function pay($data)
    {
        $reqHandler = new src\RequestHandler();

        /* 商户号，上线时务必将测试商户号替换为正式商户号 */
        $partner =  $this->tenpay_config['partner'];
        /* 密钥 */
        $key =  $this->tenpay_config['key'];
        $data['title'] = iconv('UTF-8','GB2312//IGNORE',$data['title']);
        //订单号，此处用时间加随机数生成，商户根据自己情况调整，只要保持全局唯一就行
        $out_trade_no = $data['ordersn'];
        $reqHandler->init();
        $reqHandler->setKey($key);
        $reqHandler->setGateUrl("https://gw.tenpay.com/gateway/pay.htm");
        //----------------------------------------
        //设置支付参数
        //----------------------------------------
        $reqHandler->setParameter("partner", $partner);
        $reqHandler->setParameter("out_trade_no", $out_trade_no);
        $reqHandler->setParameter("total_fee", $data['price']);  //总金额
        $reqHandler->setParameter("return_url",  $this->tenpay_config['return_url']);
        $reqHandler->setParameter("notify_url", $this->tenpay_config['notify_url']);
        $reqHandler->setParameter("body", $data['title']);
        $reqHandler->setParameter("bank_type", "DEFAULT");  	  //银行类型，默认为财付通

        //用户ip
        $reqHandler->setParameter("spbill_create_ip", $_SERVER['REMOTE_ADDR']);//客户端IP
        $reqHandler->setParameter("fee_type", "1");               //币种
        $reqHandler->setParameter("subject",$data['title']);          //商品名称，（中介交易时必填）
        //系统可选参数
        $reqHandler->setParameter("sign_type", "MD5");  	 	  //签名方式，默认为MD5，可选RSA
        $reqHandler->setParameter("service_version", "1.0"); 	  //接口版本号
        $reqHandler->setParameter("input_charset", "GBK");   	  //字符集
        $reqHandler->setParameter("sign_key_index", "1");    	  //密钥序号

        //业务可选参数
        $reqHandler->setParameter("attach", "");             	  //附件数据，原样返回就可以了
        $reqHandler->setParameter("product_fee", "");        	  //商品费用
        $reqHandler->setParameter("transport_fee", "");      	  //物流费用
        $reqHandler->setParameter("time_start", date("YmdHis"));  //订单生成时间
        $reqHandler->setParameter("time_expire", "");             //订单失效时间

        $reqHandler->setParameter("buyer_id", "");                //买方财付通帐号
        $reqHandler->setParameter("goods_tag", "");               //商品标记
        $reqHandler->setParameter("trade_mode","1");              //交易模式（1.即时到帐模式，2.中介担保模式，3.后台选择（卖家进入支付中心列表选择））
        $reqHandler->setParameter("transport_desc","");              //物流说明
        $reqHandler->setParameter("trans_type","1");              //交易类型
        $reqHandler->setParameter("agentid","");                  //平台ID
        $reqHandler->setParameter("agent_type","");               //代理模式（0.无代理，1.表示卡易售模式，2.表示网店模式）
        $reqHandler->setParameter("seller_id","");                //卖家的商户号


        //请求的URL
        $reqUrl = $reqHandler->getRequestURL();
        header("Location:" . $reqUrl);
        exit;
        //获取debug信息,建议把请求和debug信息写入日志，方便定位问题
        /**/
//        $debugInfo = $reqHandler->getDebugInfo();
//        echo "<br/>" . $reqUrl . "<br/>";
//        echo "<br/>" . $debugInfo . "<br/>";
    }

    public function returnUrl()
    {
        /* 商户号，上线时务必将测试商户号替换为正式商户号 */
        $partner =  $this->tenpay_config['partner'];
        /* 密钥 */
        $key =  $this->tenpay_config['key'];
        $resHandler = new src\ResponseHandler();
        $resHandler->setKey($key);

        //判断签名
        if($resHandler->isTenpaySign()) {

            //通知id
            $notify_id = $resHandler->getParameter("notify_id");

            //通过通知ID查询，确保通知来至财付通
            //创建查询请求
            $queryReq = new src\RequestHandler();
            $queryReq->init();
            $queryReq->setKey($key);
            $queryReq->setGateUrl("https://gw.tenpay.com/gateway/verifynotifyid.xml");
            $queryReq->setParameter("partner", $partner);
            $queryReq->setParameter("notify_id", $notify_id);

            //通信对象
            $httpClient = new src\client\TenpayHttpClient();
            $httpClient->setTimeOut(5);
            //设置请求内容
            $httpClient->setReqContent($queryReq->getRequestURL());

            //后台调用
            if($httpClient->call()) {
                //设置结果参数
                $queryRes = new src\client\ClientResponseHandler();
                $queryRes->setContent($httpClient->getResContent());
                $queryRes->setKey($key);

                //判断签名及结果
                //只有签名正确,retcode为0，trade_state为0才是支付成功
                if($queryRes->isTenpaySign() && $queryRes->getParameter("retcode") == "0" && $queryRes->getParameter("trade_state") == "0" && $queryRes->getParameter("trade_mode") == "1" ) {
                    //取结果参数做业务处理
                    $out_trade_no = $queryRes->getParameter("out_trade_no");
                    //财付通订单号
                    $transaction_id = $queryRes->getParameter("transaction_id");
                    //金额,以分为单位
                    $total_fee = $queryRes->getParameter("total_fee");
                    //如果有使用折扣券，discount有值，total_fee+discount=原请求的total_fee
                    $discount = $queryRes->getParameter("discount");

                    //------------------------------
                    //处理业务开始
                    //------------------------------

                    //处理数据库逻辑
                    //注意交易单不要重复处理
                    //!!!注意判断返回金额!!!

                    //------------------------------
                    //处理业务完毕
                    //------------------------------
                    echo "<br/>" . "支付成功" . "<br/>";

                } else {
                    //错误时，返回结果可能没有签名，写日志trade_state、retcode、retmsg看失败详情。
                    //echo "验证签名失败 或 业务错误信息:trade_state=" . $queryRes->getParameter("trade_state") . ",retcode=" . $queryRes->getParameter("retcode"). ",retmsg=" . $queryRes->getParameter("retmsg") . "<br/>" ;
                    echo "<br/>" . "支付失败" . "<br/>";
                }

                //获取查询的debug信息,建议把请求、应答内容、debug信息，通信返回码写入日志，方便定位问题
                /*
                echo "<br>------------------------------------------------------<br>";
                echo "http res:" . $httpClient->getResponseCode() . "," . $httpClient->getErrInfo() . "<br>";
                echo "query req:" . htmlentities($queryReq->getRequestURL(), ENT_NOQUOTES, "GB2312") . "<br><br>";
                echo "query res:" . htmlentities($queryRes->getContent(), ENT_NOQUOTES, "GB2312") . "<br><br>";
                echo "query reqdebug:" . $queryReq->getDebugInfo() . "<br><br>" ;
                echo "query resdebug:" . $queryRes->getDebugInfo() . "<br><br>";
                */
            }else {
                //通信失败
                //echo "fail";
                //后台调用通信失败,写日志，方便定位问题，这些信息注意保密，最好不要打印给用户
                echo "<br>订单通知查询失败:" . $httpClient->getResponseCode() ."," . $httpClient->getErrInfo() . "<br>";
            }
        } else {
            //签名错误
            echo "<br>签名失败<br>";
        }

    }

    public function notifyUrl()
    {

        /* 商户号，上线时务必将测试商户号替换为正式商户号 */
        $partner =  $this->tenpay_config['partner'];
        /* 密钥 */
        $key =  $this->tenpay_config['key'];

        /* 创建支付应答对象 */
        $resHandler = new \kevin\tenpay\src\ResponseHandler();
        $resHandler->setKey($key);

        //判断签名
        if($resHandler->isTenpaySign()) {

            //通知id
            $notify_id = $resHandler->getParameter("notify_id");

            //通过通知ID查询，确保通知来至财付通
            //创建查询请求
            $queryReq = new src\RequestHandler();
            $queryReq->init();
            $queryReq->setKey($key);
            $queryReq->setGateUrl("https://gw.tenpay.com/gateway/verifynotifyid.xml");
            $queryReq->setParameter("partner", $partner);
            $queryReq->setParameter("notify_id", $notify_id);

            //通信对象
            $httpClient = new src\client\TenpayHttpClient();
            $httpClient->setTimeOut(5);
            //设置请求内容
            $httpClient->setReqContent($queryReq->getRequestURL());

            //后台调用
            if($httpClient->call()) {
                //设置结果参数
                $queryRes = new src\client\ClientResponseHandler();
                $queryRes->setContent($httpClient->getResContent());
                $queryRes->setKey($key);

                //判断签名及结果
                //只有签名正确,retcode为0，trade_state为0才是支付成功
                if($queryRes->isTenpaySign() && $queryRes->getParameter("retcode") == "0" && $queryRes->getParameter("trade_state") == "0" && $queryRes->getParameter("trade_mode") == "1" ) {
                    //取结果参数做业务处理
                    $out_trade_no = $queryRes->getParameter("out_trade_no");
                    //财付通订单号
                    $transaction_id = $queryRes->getParameter("transaction_id");
                    //金额,以分为单位
                    $total_fee = $queryRes->getParameter("total_fee");
                    //如果有使用折扣券，discount有值，total_fee+discount=原请求的total_fee
                    $discount = $queryRes->getParameter("discount");

                    //------------------------------
                    //处理业务开始
                    //------------------------------

                    //处理数据库逻辑
                    //注意交易单不要重复处理
                    //注意判断返回金额

                    //------------------------------
                    //处理业务完毕
                    //------------------------------
                    echo "success";

                } else {
                    //错误时，返回结果可能没有签名，写日志trade_state、retcode、retmsg看失败详情。
                    //echo "验证签名失败 或 业务错误信息:trade_state=" . $queryRes->getParameter("trade_state") . ",retcode=" . $queryRes->getParameter("retcode"). ",retmsg=" . $queryRes->getParameter("retmsg") . "<br/>" ;
                    echo "fail";
                }

                //获取查询的debug信息,建议把请求、应答内容、debug信息，通信返回码写入日志，方便定位问题
                /*
                echo "<br>------------------------------------------------------<br>";
                echo "http res:" . $httpClient->getResponseCode() . "," . $httpClient->getErrInfo() . "<br>";
                echo "query req:" . htmlentities($queryReq->getRequestURL(), ENT_NOQUOTES, "GB2312") . "<br><br>";
                echo "query res:" . htmlentities($queryRes->getContent(), ENT_NOQUOTES, "GB2312") . "<br><br>";
                echo "query reqdebug:" . $queryReq->getDebugInfo() . "<br><br>" ;
                echo "query resdebug:" . $queryRes->getDebugInfo() . "<br><br>";
                */
            }else {
                //通信失败
                echo "fail";
                //后台调用通信失败,写日志，方便定位问题
                //echo "<br>call err:" . $httpClient->getResponseCode() ."," . $httpClient->getErrInfo() . "<br>";
            }


        } else {
            //回调签名错误
            echo "fail";
            //echo "<br>签名失败<br>";
        }

        //获取debug信息,建议把debug信息写入日志，方便定位问题
        //echo $resHandler->getDebugInfo() . "<br>";

    }
}