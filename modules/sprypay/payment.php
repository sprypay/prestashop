<?php
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../init.php');
include(dirname(__FILE__) . '/sprypay.php');
include(dirname(__FILE__) . '/sprypaylib/SprypayRequestPaymentForm.php');

$sprypay    = new Sprypay();
$cartId     = (int) $cookie->id_cart;
$customerId = (int) $cookie->id_customer;
$cart     = new Cart($cartId);
$customer = new Customer($customerId);
if (!Validate::isLoadedObject($cart) || !Validate::isLoadedObject($customer))
    die('Cart or customer not found');

$currency = new Currency(intval($cart->id_currency));
$total = $cart->getOrderTotal(true, 3);
$script_status = Configuration::get('SPRYPAY_SCRIPT_STATUS');

if($script_status == 'before')
{
    // check if the order status is defined
    if (!defined('_PS_OS_SPRYPAY_')) {
        // order status is not defined - check if, it exists in the table
        $rq = Db::getInstance()->getRow('
	SELECT `id_order_state` FROM `'._DB_PREFIX_.'order_state_lang`
	WHERE id_lang = \''.pSQL('1').'\' AND  template = \''.pSQL('sprypay').'\'');
        if ($rq && isset($rq['id_order_state']) && intval($rq['id_order_state']) > 0) {
            // order status exists in the table - define it.
            define('_PS_OS_SPRYPAY_', $rq['id_order_state']);
        } else {
            // order status doesn't exist in the table
            // insert it into the table and then define it.
            Db::getInstance()->Execute('
		INSERT INTO `'._DB_PREFIX_.'order_state` (`send_email`, `module_name`, `unremovable`, `color`) VALUES(1,\'sprypay\',1, \'RoyalBlue\')');
            $stateid = Db::getInstance()->Insert_ID();
            Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'order_state_lang` (`id_order_state`, `id_lang`, `name`, `template`)
		VALUES(' . intval($stateid) . ', 1, \''.$sprypay->l('Pending payment SpryPay').'\',\'sprypay\')');
            define('_PS_OS_SPRYPAY_', $stateid);
        }
    }
    $sprypay->validateOrder(intval($cart->id), _PS_OS_SPRYPAY_, $total, $sprypay->displayName,NULL,NULL, NULL, false, $cart->secure_key);
}

$shopId = Configuration::get('SPRYPAY_SHOP_ID');
$requestPaymentForm = new SprypayRequestPaymentForm();
$requestPaymentForm->setShopId($shopId);
$requestPaymentForm->setShopPaymentId($cart->id);
$requestPaymentForm->setAmount(floatval($cart->getOrderTotal()));
$requestPaymentForm->setCurrency(strtolower($currency->iso_code));
$requestPaymentForm->setPurpose($sprypay->l('Cart №').$cart->id);
$requestPaymentForm->setUserEmail($customer->email);
$requestPaymentForm->setSubmitLabel($sprypay->l('Go to SpryPay'));

// Detecting language
if (!$cookie->id_lang) $langId = (int) Configuration::get('PS_LANG_DEFAULT');
else $langId = (int) $cookie->id_lang;
$language = strtolower(Language::getIsoById($langId));
// Sprypay support only Russian and English languages
if ($language != 'ru') $language = 'en';

$requestPaymentForm->setLanguage($language);

$smarty->assign(array(
	'redirecting_text' => $sprypay->l('Please wait, redirecting to SpryPay... Thanks.'),
    'payment_form'  => $requestPaymentForm->renderForm(),
));

$smarty->display(dirname(__FILE__).'/templates/redirect.tpl');
?>
