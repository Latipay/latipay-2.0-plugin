REPLACE INTO cscart_payment_processors (`processor`,`processor_script`,`processor_template`,`admin_template`,`callback`,`type`, `addon`) VALUES ('LatipayV2','latipayv2.php', 'views/orders/components/payments/processors/latipayv2.tpl','latipayv2.tpl', 'Y', 'P', '');
REPLACE INTO cscart_language_values (`lang_code`,`name`,`value`) VALUES ('EN','user_id','Latipay User Id');
REPLACE INTO cscart_language_values (`lang_code`,`name`,`value`) VALUES ('EN','api_key','Latipay Api-Key');
REPLACE INTO cscart_language_values (`lang_code`,`name`,`value`) VALUES ('EN','wallet_id','Latipay Wallet Id');
REPLACE INTO cscart_language_values (`lang_code`,`name`,`value`) VALUES ('EN','access_url','Latipay Gateway URL');
REPLACE INTO cscart_language_values (`lang_code`,`name`,`value`) VALUES ('EN','alipay','Alipay');
REPLACE INTO cscart_language_values (`lang_code`,`name`,`value`) VALUES ('EN','wechat','Wechat');
REPLACE INTO cscart_language_values (`lang_code`,`name`,`value`) VALUES ('EN','onlinebank','Online Bank');
REPLACE INTO cscart_language_values (`lang_code`,`name`,`value`) VALUES ('EN','merchant_reference','Merchant Reference');