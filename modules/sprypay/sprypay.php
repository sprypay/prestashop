<?php
if (!defined('_PS_VERSION_'))
    exit;
/*
	Plugin Name: SpryPay Payment Gateway for PrestoShop
	Plugin URI: http://sprypay.ru/moduli-oplaty/prestoshop/
	Author: Sprypay.ru
	Author URI: http://sprypay.ru
*/
class Sprypay extends PaymentModule
{
	public function __construct()
    {
        $this->name    = 'sprypay';
        $this->tab     = 'payments_gateways';
        $this->version = '1.4';
        $this->author = 'sprypay.ru';
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        parent::__construct();

        $this->_errors = array();
        $this->page = basename(__FILE__, '.php');
        $this->displayName = $this->l('SpryPay');
        $this->description = $this->l('Accepts payments by WebMoney, Yandex, Qiwi, W1, PerfectMoney, Visa/MasterCard, SMS, Wire transfer and other');
        $this->confirmUninstall = $this->l('Are you sure you want to delete your details?');
    }

	public function install()
	{
		if (!parent::install()
			|| !$this->registerHook('payment')
			|| !$this->registerHook('paymentReturn'))
			return false;

		Configuration::updateValue('SPRYPAY_SHOP_ID', '');
		Configuration::updateValue('SPRYPAY_SHOP_SECRET', '');
        Configuration::updateValue('SPRYPAY_SCRIPT_STATUS', 'before');

		return true;
	}

	public function uninstall()
	{
		Configuration::deleteByName('SPRYPAY_SHOP_ID');
		Configuration::deleteByName('SPRYPAY_SHOP_SECRET');
        Configuration::deleteByName('SPRYPAY_SCRIPT_STATUS');

		return parent::uninstall();
	}

    public function getContent()
    {
        if (isset($_REQUEST['shopId']) && $_REQUEST['shopSecret'] && $_REQUEST['script_status']) {
            Configuration::updateValue('SPRYPAY_SHOP_ID', (int) $_REQUEST['shopId']);
            Configuration::updateValue('SPRYPAY_SHOP_SECRET', $_REQUEST['shopSecret']);
            Configuration::updateValue('SPRYPAY_SCRIPT_STATUS', $_REQUEST['script_status']);

        }
        return $this->getModuleSettingsForm();
    }

	private function getModuleSettingsForm()
	{
		$shopId     = Configuration::get('SPRYPAY_SHOP_ID');
		$shopSecret = Configuration::get('SPRYPAY_SHOP_SECRET');
        $script_status = Configuration::get('SPRYPAY_SCRIPT_STATUS');


        $html='<form method="post" style="clear: both;">
            <fieldset>
            <legend><img src="../img/admin/contact.gif" />'.$this->l('Settings').'</legend>
            <label>'.$this->l('Shop Id').'</label>
            <div class="margin-form"><input type="text" size="32" name="shopId" value="'.htmlentities($shopId, ENT_COMPAT, 'UTF-8').'" /></div>
            <label>'.$this->l('Secret').'</label>
            <div class="margin-form"><input type="text" size="32" name="shopSecret" value="'.htmlentities($shopSecret, ENT_COMPAT, 'UTF-8').'" /></div>
            <label>'.$this->l('Script of payment').'</label>
            <div class="margin-form">
                <input type="radio" size="32" name="script_status" value="before" ';
        if($script_status=='before') $html.=" checked ";
        $html.=' /> <b>'.$this->l('Before - Order will be in the system BEFORE payment -  default').' </b><br>
            <input type="radio" size="32" name="script_status" value="after"';
        if($script_status=='after') $html.=" checked ";
        $html.=' /> <b>'.$this->l('After - Order will be in the system AFTER payment').'</b>
            </div>
            <br /><center><input type="submit" name="submitSprypay" value="'.$this->l('Update settings').'" class="button" /></center>
            </fieldset>
            </form>';

        return $html;
	}

	public function hookPayment($params)
	{
        global $smarty;
        $protocol = 'http'; //$_SERVER['HTTPS'] ? 'https' :
        $smarty->assign(array(
            'url'=> $protocol.'://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__."modules/{$this->name}"));

			return $this->display(__FILE__, 'templates/payment.tpl');


	}

	public function hookPaymentReturn($params)
	{
		if (!$this->active)
			return ;

		return $this->display(__FILE__, 'templates/confirmation.tpl');
	}


}
