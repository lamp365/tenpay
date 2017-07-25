<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 2017/7/25
 * Time: 18:54
 */
include './vendor/autoload.php';
$tenpay_config = array(
    'partner' => '1486474181',
    'key' => '4ff7a938fc9489b17f7b9b4f5127c59a',
    'return_url' => 'http://baidu.com',
    'notify_url' => 'http://baidu.com',
);
$a = new kevin365\tenpay\Tenpay($tenpay_config);
$data['ordersn'] = date('YmdHis').uniqid();
$data['title']   = '测试商品';
$data['price']   = '1';
$a->pay($data);