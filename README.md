# tenpay
个人封装的一个composer组件，财付通支付组件
#使用如下
include './vendor/autoload.php';
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

#composer地址
https://packagist.org/packages/kevin365/tenpay
