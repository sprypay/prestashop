<?php
/*
	Plugin Name: SpryPay Payment Gateway for PrestoShop
	Plugin URI: http://sprypay.ru/moduli-oplaty/prestoshop/
	Author: Sprypay.ru
	Author URI: http://sprypay.ru
*/
class SprypayRequestPaymentForm
{
    private $formData = array();
    private $formId = 'sprypayForm';
    private $formMethod = 'post';
    private $formAction = 'https://sprypay.ru/sppi/';
    private $submitLabel = 'submit';

    public function setShopId($shopId)
    {
        $this->formData['spShopId'] = $shopId;
    }

    public function setShopPaymentId($shopPaymentId)
    {
        $this->formData['spShopPaymentId'] = $shopPaymentId;
    }

    public function setAmount($amount)
    {
        $this->formData['spAmount'] = $amount;
    }

    public function setCurrency($currency)
    {
        $this->formData['spCurrency'] = $currency;
    }

    public function setPurpose($purpose)
    {
        $this->formData['spPurpose'] = $purpose;
    }

    public function setUserEmail($userEmail)
    {
        $this->formData['spUserEmail'] = $userEmail;
    }

    public function setUserData(array $userData)
    {
        foreach ($userData as $name => $value)
            $this->formData['spUserData'.$name] = $value;
    }

    public function setLanguage($language)
    {
        $this->formData['lang'] = $language;
    }

    public function setSuccessUrl($successUrl)
    {
        $this->formData['spSuccessUrl'] = $successUrl;
    }

    public function setSuccessMethod($successMethod)
    {
        if ($successMethod == 'post')
            $this->formData['spSuccessMethod'] = 1;
        elseif ($successMethod == 'get')
            $this->formData['spSuccessMethod'] = 0;
        else
            $this->formData['spSuccessMethod'] = $successMethod;
    }

    public function setFailUrl($failUrl)
    {
        $this->formData['spFailUrl'] = $failUrl;
    }

    public function setFailMethod($failMethod)
    {
        if ($failMethod == 'post')
            $this->formData['spFailMethod'] = 1;
        elseif ($failMethod == 'get')
            $this->formData['spFailMethod'] = 0;
        else
            $this->formData['spFailMethod'] = $failMethod;
    }

    public function setIpnUrl($ipnUrl)
    {
        $this->formData['spIpnUrl'] = $ipnUrl;
    }

    public function setIpnMethod($ipnMethod)
    {
        if ($ipnMethod == 'post')
            $this->formData['spIpnMethod'] = 1;
        elseif ($ipnMethod == 'get')
            $this->formData['spIpnMethod'] = 0;
        else
            $this->formData['spIpnMethod'] = $ipnMethod;
    }

    public function setAllowedPaymentSystems(array $allowedPaymentSystems)
    {
        $this->formData['spSelectedPS'] = implode(',', $allowedPaymentSystems);
    }

    public function setForbiddenPaymentSystems(array $forbiddenPaymentSystems)
    {
        $this->formData['spForbiddenPS'] = implode(',', $forbiddenPaymentSystems);
    }

    public function setMethod($method)
    {
        if ($method != 'get' && $method != 'post')
            $method = 'post';
        $this->formMethod = $method;
    }

    public function setSubmitLabel($label)
    {
        $this->submitLabel = $label;
    }

    public function renderForm()
    {
        print_r($_POST);
        $form  = '<form id ="'.$this->formId.'" action="'.$this->formAction.'" method="'.$this->formMethod.'">';
        foreach ($this->formData as $name => $value)
            $form .= $this->renderInputField($name, $value);
        $form .= '<input type="hidden" name="src" value="1"/>';
        $form .= '<input type="submit" value="'.$this->submitLabel.'"/>';
        $form .= '</form>';
        $form .= '<script type="text/javascript"> document.forms["'.$this->formId.'"].submit(); </script>';
        return $form;
    }

    private function renderInputField($name, $value)
    {
        return '<input type="hidden" name="'.$name.'" value="'.$value.'" />';
    }
}
?>
