<?php defined('SYSPATH') or die('No direct script access.');

return array(
	//linkpoint gateway
	'linkpoint'=>array(
		// Linkpoint storenumber
		'storenumber' => '1909059978',
		//Keyfile path,should has 'r' permisson
		'keyfile' => 'c:/htdocs/linkpt.test/cert.pem',
		// Linkpoint environment: live, sandbox
		'environment' => 'sandbox',
	),
	//paypal gateway
	'paypal'=>array(
		// PayPal API and username
		'username' => '_wpp_1303740260_biz_api1.mail.ru',
		'password' => '1303740314',
		// PayPal API signature
		'signature' => 'AIL3--q.cxyqbaLRuQNVK.GC0II7AUfpJZu3kvKZc-AIABC1v34-avNF',
		//merchant id and email
		'merchant_email'	=> '_wpp_1303740260_biz@mail.ru',
		'merchant_id' => 'PES2WUN4VA5UW',
		// PayPal environment: live, sandbox, beta-sandbox
		'environment' => 'sandbox'
	)
);
