<?php
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__).'/sprypay.php');
include(dirname(__FILE__).'/sprypaylib/SprypayNotificationValidator.php');

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
$id_order=Db::getInstance()->getValue('SELECT `id_order` FROM `'._DB_PREFIX_.'orders` WHERE `id_cart` = \''.pSQL($cartId).'\'');
$id_currency = Db::getInstance()->getValue('SELECT `id_currency` FROM `'._DB_PREFIX_.'orders` WHERE `id_cart` = \''.pSQL($cartId).'\'');
$total_paid = Db::getInstance()->getValue('SELECT `total_paid` FROM `'._DB_PREFIX_.'orders` WHERE `id_cart` = \''.pSQL($cartId).'\'');
$secure_key= Db::getInstance()->getValue('SELECT `secure_key` FROM `'._DB_PREFIX_.'orders` WHERE `id_cart` = \''.pSQL($cartId).'\'');

$shopId     = Configuration::get('SPRYPAY_SHOP_ID');
$shopSecret = Configuration::get('SPRYPAY_SHOP_SECRET');

if (!$validator->validateShopId($shopId))
    die('error Invalid shop id: '.$notificationData['spShopId']);

if (!$validator->validateAmount($total_paid))
    die('error Invalid amount: '.$notificationData['spAmount']);

$currency = new Currency(intval($id_currency));
if (!$validator->validateCurrency(strtolower($currency->iso_code)))
    die('error Invalid currency: '.$notificationData['spCurrency']);

if (!$validator->validateControlSum($shopSecret))
    die('error Invalid control sum: '.$notificationData['spHashString']);

$orderState = Configuration::get('PS_OS_PAYMENT');
$amountPaid = $notificationData['spAmount'];
$message = 'Sprypay payment '.$notificationData['spPaymentId'].' ('.$notificationData['spBalanceAmount'].' '.$notificationData['spBalanceCurrency'].') was enrolled to sprypay balance in '.$notificationData['spEnrollDateTime'];

//$sprypay->validateOrder(intval($id_order), $orderState, $amountPaid, $sprypay->displayName, $message,NULL, NULL, false, $secure_key);

$history = new OrderHistory();// Объект История заказов
$history->id_order = (int)$id_order;//Получение данных о заказе через id заказа
$history->changeIdOrderState((int)$orderState, (int)$history->id_order);
$history->addWithemail(true);
die($validator->confirmNotification());

?>
