<?php
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../init.php');
include(dirname(__FILE__) . '/sprypay.php');
include(dirname(__FILE__) . '/sprypaylib/SprypayNotificationValidator.php');

$sprypay = new Sprypay();
$validator = new SprypayNotificationValidator();
if ($_SERVER['REQUEST_METHOD'] == 'GET') $notificationData = $_GET;
elseif ($_SERVER['REQUEST_METHOD'] == 'POST') $notificationData = $_POST;
else die('Unsupported request method: '.$_SERVER['REQUEST_METHOD']);

try {
    $validator->spParams = $notificationData;
} catch (Exception $e) {
    die($e->getMessage());
}
$notificationData = $validator->getNotificationData();

$cartId = (int) $notificationData['spShopPaymentId'];
$cart =new Cart($cartId);
//$id_order=Db::getInstance()->getValue('SELECT `id_order` FROM `'._DB_PREFIX_.'orders` WHERE `id_cart` = \''.pSQL($cartId).'\'');
//$id_currency = Db::getInstance()->getValue('SELECT `id_currency` FROM `'._DB_PREFIX_.'orders` WHERE `id_cart` = \''.pSQL($cartId).'\'');
//$total_paid = Db::getInstance()->getValue('SELECT `total_paid` FROM `'._DB_PREFIX_.'orders` WHERE `id_cart` = \''.pSQL($cartId).'\'');
//$secure_key= Db::getInstance()->getValue('SELECT `secure_key` FROM `'._DB_PREFIX_.'orders` WHERE `id_cart` = \''.pSQL($cartId).'\'');

$shopId     = Configuration::get('SPRYPAY_SHOP_ID');
$shopSecret = Configuration::get('SPRYPAY_SHOP_SECRET');
$script_status = Configuration::get('SPRYPAY_SCRIPT_STATUS');

if (!$validator->validateControlSum($shopSecret))
    die('error Invalid control sum: '.$notificationData['spHashString']);

$orderState = Configuration::get('PS_OS_PAYMENT');
$amountPaid = $notificationData['spAmount'];
$message = 'SpryPay №'.$notificationData['spPaymentId'].' ('.$notificationData['spBalanceAmount'].' '.$notificationData['spBalanceCurrency'].')'.$sprypay->l(' was enrolled to sprypay balance in ').$notificationData['spEnrollDateTime'];

if($script_status == 'before')
{
    $id_order=Db::getInstance()->getValue('SELECT `id_order` FROM `'._DB_PREFIX_.'orders` WHERE `id_cart` = \''.pSQL($cartId).'\'');
    $history = new OrderHistory();
    $history->id_order = (int)$id_order;
    $history->changeIdOrderState((int)$orderState, (int)$history->id_order);
    $history->addWithemail(true);
}
elseif($script_status == 'after')
{
    $sprypay->validateOrder($cartId, _PS_OS_PAYMENT_, $amountPaid, $sprypay->displayName, $message,NULL, NULL, false, $cart->secure_key);
}
die($validator->confirmNotification());

?>
