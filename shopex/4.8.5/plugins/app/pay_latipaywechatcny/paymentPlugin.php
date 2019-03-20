<?php
function payCallBack($return)
{

    require(dirname(__FILE__) . '/../../loader.php');
    $oPay = &$system->loadModel('trading/payment');
    $file = basename($_SERVER["PHP_SELF"]);
    $fileArr = explode('_', $file);
    $fileArrs = explode('.', $fileArr[1]);
    $gateWayId = $fileArrs[0];

    $serverCall = preg_match("/^pay\_([^\.]+)\.server\.php$/i", $file, $matches) ? $matches[1] : false;
    if ($serverCall) {
        require('pay_' . $gateWayId . '.server.php');
        $func_name = "pay_" . $serverCall . "_callback";
        $className = "pay_" . $serverCall;
        $o = new $className($system);
        //$status = $func_name($return,$paymentId,$tradeno);
        $status = $o->$func_name($return, $paymentId, $money, $message, $tradeno);
        $info = array('money' => $money, 'memo' => $message, 'trade_no' => $tradeno);
        $result = $oPay->setPayStatus($paymentId, $status, $info);
    } else {
        require('pay_' . $gateWayId . '.php');
        $money = null;
        $status = null;
        $className = 'pay_' . $gateWayId;
        $o = new $className($system);

        $status = $o->callback($return, $paymentId, $money, $message, $tradeno);
        $result = $oPay->progress($paymentId, $status, array('money' => $money, 'memo' => $message, 'trade_no' => $tradeno));
    }
}

payCallBack(array_merge($_GET, $_POST));
exit();
?>
