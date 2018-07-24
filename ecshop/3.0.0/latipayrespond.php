<?php
/*
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * 
 * 
 */
define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'includes/lib_payment.php');
require(ROOT_PATH . 'includes/lib_order.php');
/* 支付方式代码 */
if ($_GET['payment_method'] == 'wechat') {
    $pay_code = 'latipaywechat';
}
if ($_GET['payment_method'] == 'alipay') {
    $pay_code = 'latipayalipay';
}
if ($_GET['payment_method'] == 'onlineBank') {
    $pay_code = 'latipayonlinebank';
}

/* 判断是否启用 */
$sql = "SELECT * FROM " . $ecs->table('payment') . " WHERE pay_code = '$pay_code' AND enabled = 1";
$payment_info = $db->getRow($sql);
if (!isset($payment_info['pay_id']) || !isset($payment_info['pay_config'])) {
    $msg = $_LANG['pay_disabled'];
} else {
    $plugin_file = 'includes/modules/payment/' . $pay_code . '.php';

    /* 检查插件文件是否存在，如果存在则验证支付是否成功，否则则返回失败信息 */
    if (file_exists($plugin_file)) {
        /* 根据支付方式代码创建支付类的对象并调用其响应操作方法 */
        include_once($plugin_file);

        $payment = new $pay_code();

        //取得支付信息，生成支付代码
        $paymentConfig = unserialize_config($payment_info['pay_config']);

        $msg = (@$payment->respond($paymentConfig)) ? $_LANG['pay_success'] : $_LANG['pay_fail'];
    } else {
        $msg = $_LANG['pay_not_exist'];
    }
}

assign_template();
$position = assign_ur_here();
$smarty->assign('page_title', $position['title']);
// 页面标题
$smarty->assign('ur_here', $position['ur_here']);
// 当前位置
$smarty->assign('page_title', $position['title']);
// 页面标题
$smarty->assign('ur_here', $position['ur_here']);
// 当前位置
$smarty->assign('helps', get_shop_help());
// 网店帮助

$smarty->assign('message', $msg);
$smarty->assign('shop_url', $ecs->url());

$smarty->display('respond.dwt');
?>
