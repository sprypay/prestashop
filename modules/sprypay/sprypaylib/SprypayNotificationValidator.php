<?php
/*
	Plugin Name: SpryPay Payment Gateway for PrestoShop
	Plugin URI: http://sprypay.ru/moduli-oplaty/prestoshop/
	Author: Sprypay.ru
	Author URI: http://sprypay.ru
*/
class SprypayNotificationValidator
{
    private $spPaymentId;
    private $spShopId;
    private $spShopPaymentId;
    private $spAmount;
    private $spCurrency;
    private $spBalanceAmount;
    private $spBalanceCurrency;
    private $spCustomerEmail;
    private $spPurpose;
    private $spPaymentSystemId;
    private $spPaymentSystemAmount;
    private $spPaymentSystemPaymentId;
    private $spEnrollDateTime;
    private $spHashString;
    private $spUserData = array();

    private $requiredParams = array(
        'spPaymentId',
        'spShopId',
        'spShopPaymentId',
        'spAmount',
        'spCurrency',
        'spBalanceAmount',
        'spCustomerEmail',
        'spPurpose',
        'spPaymentSystemId',
        'spPaymentSystemAmount',
        'spPaymentSystemPaymentId',
        'spEnrollDateTime',
    );

    /**
     * Конструктор класса.
     *
     * @param array $data данные пришедшие с оповещением о платеже.
     * @access public
     * @return void
     */
    public function __construct(array $data = array())
    {
        if (!empty($data))
            $this->parseNotificationParams($data);
    }

    /**
     * Осуществляет разбор данных о платеже.
     *
     * @param array $params
     * @access private
     * @return void
     */
    private function parseNotificationParams(array $params)
    {
        foreach ($this->getNotificationParamsNames() as $paramName) {
            if (isset($params[$paramName]))
                $this->$paramName = $params[$paramName];
            elseif ($this->isParamRequired($paramName))
                throw new Exception('Param '.$paramName.' reqiuired');
        }
        $this->parseUserData($params);
    }

    /**
     * Возвращает - является ли параметр обязательным.
     *
     * @param string $paramName
     * @access private
     * @return bool
     */
    private function isParamRequired($paramName)
    {
        return in_array($paramName, $this->requiredParams);
    }

    /**
     * Производит разбор дополнительных данных продавца.
     *
     * @param array $data
     * @access private
     * @return void
     */
    private function parseUserData(array $data)
    {
        foreach ($data as $name => $value) {
            if (substr($name, 0, 10) == 'spUserData') {
                $newName = substr($name, 10);
                $this->spUserData[$newName] = $value;
            }
        }
    }

    /**
     * Возвращает имена параметров которые дяолжны придти в оповещении о платеже.
     *
     * @access private
     * @return array
     */
    private function getNotificationParamsNames()
    {
        static $paramsNames = null;
        if ($paramsNames === null) {
            $paramsNames = array();
            $classVars = array_keys(get_class_vars(__CLASS__));
            foreach ($classVars as $varName)
                if (substr($varName, 0, 2) == 'sp')
                    $paramsNames[] = $varName;
        }
        return $paramsNames;
    }

    /**
     * Возвращает все данные из оповещения о платеже.
     *
     * @access public
     * @return array
     */
    public function getNotificationData()
    {
        $notificationData = array();
        foreach ($this->getNotificationParamsNames() as $name) {
            //$newName = lcfirst(substr($name, 2));
            $notificationData[$name] = $this->$name;
        }
        return $notificationData;
    }

    /**
     * Вычисление контрольной подписи по правилам Sprypay.
     *
     * @param $secretKey секретный ключ магазина
     * @access public
     * @return string
     */
    public function calculateControlSum($secretKey)
    {
        $splitString = $this->spPaymentId.$this->spShopId.$this->spShopPaymentId.$this->spBalanceAmount.
        $this->spAmount.$this->spCurrency.$this->spCustomerEmail.$this->spPurpose.$this->spPaymentSystemId.
        $this->spPaymentSystemAmount.$this->spPaymentSystemPaymentId.$this->spEnrollDateTime.$secretKey;
        $sign = md5($splitString);
        return $sign;
    }

    /**
     * Проверка идентификатора магазина.
     *
     * @param int $shopId корректный идентификатор магазина
     * @access public
     * @return bool
     */
    public function validateShopId($shopId)
    {
        return ($this->spShopId == $shopId);
    }

    /**
     * Проверка валюты.
     *
     * @param string $currency корректная валюта платежа
     * @access public
     * @return bool
     */
    public function validateCurrency($currency)
    {
        $currency = strtolower($currency);
        // Хак. Т.к. при оповещении Sprypay всегда возвращает rur,
        // даже если в форме запроса платежа был указан rub
        if ($this->spCurrency == 'rur' && $currency == 'rub') return true;
        return ($this->spCurrency == $currency);
    }

    /**
     * Проверка суммы платежа.
     *
     * @param float $amount сумма платежа
     * @access public
     * @return bool
     */
    public function validateAmount($amount)
    {
        return ($this->spAmount == $amount);
    }

    /**
     * Проверка контрольной суммы платежа.
     *
     * @param string $secretKey секретный ключ магазина
     * @access public
     * @return bool
     */
    public function validateControlSum($secretKey)
    {
        return($this->spHashString == $this->calculateControlSum($secretKey));
    }

    /**
     * Подтверждение уведомления о платеже.
     *
     * @access public
     * @return string
     */
    public function confirmNotification()
    {
        return 'ok';
    }

    public function __set($name, $value)
    {
        if ($name == 'spParams')
            if (is_array($value))
                $this->parseNotificationParams($value);
        else
            throw Exception('Unknown property '.$name);
    }
}
?>
