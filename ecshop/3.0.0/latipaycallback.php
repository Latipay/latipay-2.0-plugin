<?php
define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'includes/lib_payment.php');
require(ROOT_PATH . 'includes/lib_order.php');
/* 支付方式代码 */
if ($_POST['payment_method'] == 'wechat') {
    $pay_code = 'latipaywechat';
}
if ($_POST['payment_method'] == 'alipay') {
    $pay_code = 'latipayalipay';
}
if ($_POST['payment_method'] == 'onlineBank') {
    $pay_code = 'latipayonlinebank';
}

if (!$pay_code) {
    die('access denied');
}

/* 判断是否启用 */
$sql = "SELECT * FROM " . $ecs->table('payment') . " WHERE pay_code = '$pay_code' AND enabled = 1";
$payment_info = $db->getRow($sql);
if (!isset($payment_info['pay_id']) || !isset($payment_info['pay_config'])) {
    die('pay config error');
} else {
    $plugin_file = 'includes/modules/payment/' . $pay_code . '.php';

    /* 检查插件文件是否存在，如果存在则验证支付是否成功，否则则返回失败信息 */
    if (file_exists($plugin_file)) {
        /* 根据支付方式代码创建支付类的对象并调用其响应操作方法 */
        include_once($plugin_file);

        $payment = new $pay_code();
        $paymentConfig = unserialize_config($payment_info['pay_config']);
        $msg = (@$payment->callback_confirm($paymentConfig)) ? 'sent' : 'error';

    } else {
        $msg = $_LANG['pay_not_exist'];
    }

    die($msg);
}

?>
