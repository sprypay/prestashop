<?php
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__).'/sprypay.php');
include(dirname(__FILE__).'/sprypaylib/SprypayRequestPaymentForm.php');

$sprypay    = new Sprypay();
$cartId     = (int) $cookie->id_cart;
$customerId = (int) $cookie->id_customer;
$cart     = new Cart($cartId);
$customer = new Customer($customerId);
if (!Validate::isLoadedObject($cart) || !Validate::isLoadedObject($customer))
    die('Cart or customer not found');

$currency = new Currency(intval($cart->id_currency));
$total = $cart->getOrderTotal(true, 3);
$sprypay->validateOrder(intval($cart->id), _PS_OS_PREPARATION_, $total, $sprypay->displayName);

$shopId = Configuration::get('SPRYPAY_SHOP_ID');
$requestPaymentForm = new SprypayRequestPaymentForm();
$requestPaymentForm->setShopId($shopId);
$requestPaymentForm->setShopPaymentId($cart->id);
$requestPaymentForm->setAmount(floatval($cart->getOrderTotal()));
$requestPaymentForm->setCurrency(strtolower($currency->iso_code));
$requestPaymentForm->setPurpose('Cart #'.$cart->id);
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
