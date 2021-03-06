<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>订单查询后台调用示例</title>
</head>
<body>

<?php
//---------------------------------------------------------
//财付通订单查后台调用示例，商户按照此文档进行开发即可
//---------------------------------------------------------

require (__DIR__."/src/RequestHandler.php");
require (__DIR__ . "/src/client/ClientResponseHandler.php");
require (__DIR__ . "/src/client/TenpayHttpClient.php");

/* 商户号 */
$partner = "1486474181";


/* 密钥 */
$key = "4ff7a938fc9489b17f7b9b4f5127c59a";




/* 创建支付请求对象 */
$reqHandler = new \kevin365\tenpay\src\RequestHandler();

//通信对象
$httpClient = new \kevin365\tenpay\src\client\TenpayHttpClient();

//应答对象
$resHandler = new \kevin365\tenpay\src\client\ClientResponseHandler();

//-----------------------------
//设置请求参数
//-----------------------------
$reqHandler->init();
$reqHandler->setKey($key);

$reqHandler->setGateUrl("https://gw.tenpay.com/gateway/normalrefundquery.xml");
$reqHandler->setParameter("partner", $partner);
//out_trade_no和transaction_id、out_refund_no、refund_id至少一个必填，
//同时存在时以优先级高为准，优先级为：refund_id>out_refund_no>transaction_id>out_trade_no
$reqHandler->setParameter("out_trade_no", "201101121111462844");
//$reqHandler->setParameter("transaction_id", "1900000109201101120023707085");



//-----------------------------
//设置通信参数
//-----------------------------
$httpClient->setTimeOut(5);
//设置请求内容
$httpClient->setReqContent($reqHandler->getRequestURL());

//后台调用
if($httpClient->call()) {
	//设置结果参数
	$resHandler->setContent($httpClient->getResContent());
	$resHandler->setKey($key);

	//判断签名及结果
	//只有签名正确并且retcode为0才是请求成功
	if($resHandler->isTenpaySign() && $resHandler->getParameter("retcode") == "0" ) {
		//取结果参数做业务处理
		//商户订单号
		$out_trade_no = $resHandler->getParameter("out_trade_no");

		//财付通订单号
		$transaction_id = $resHandler->getParameter("transaction_id");

		//金额,以分为单位
		$refund_count = $resHandler->getParameter("refund_count");

		echo "退款笔数:" . $refund_count;

		//每笔退款详情
		for($i=0; $i<$refund_count; $i++) {
			echo "第" . ($i+1) . "笔：" . "refund_state_" . $i . "=" . $resHandler->getParameter("refund_state_".$i) . ",out_refund_no_" . $i . "=" . $resHandler->getParameter("out_refund_no_".$i) . ",refund_fee_" . $i . "=" . $resHandler->getParameter("refund_fee_".$i) . "<br>";;

		}



	} else {
		//错误时，返回结果可能没有签名，记录retcode、retmsg看失败详情。
		echo "验证签名失败 或 业务错误信息:retcode=" . $resHandler->getParameter("retcode"). ",retmsg=" . $resHandler->getParameter("retmsg") . "<br>";
	}

} else {
	//后台调用通信失败
	echo "call err:" . $httpClient->getResponseCode() ."," . $httpClient->getErrInfo() . "<br>";
	//有可能因为网络原因，请求已经处理，但未收到应答。
}


//调试信息,建议把请求、应答内容、debug信息，通信返回码写入日志，方便定位问题
/*
echo "<br>------------------------------------------------------<br>";
echo "http res:" . $httpClient->getResponseCode() . "," . $httpClient->getErrInfo() . "<br>";
echo "req:" . htmlentities($reqHandler->getRequestURL(), ENT_NOQUOTES, "GB2312") . "<br><br>";
echo "res:" . htmlentities($resHandler->getContent(), ENT_NOQUOTES, "GB2312") . "<br><br>";
echo "reqdebug:" . $reqHandler->getDebugInfo() . "<br><br>" ;
echo "resdebug:" . $resHandler->getDebugInfo() . "<br><br>";
*/

?>


</body>
</html>
