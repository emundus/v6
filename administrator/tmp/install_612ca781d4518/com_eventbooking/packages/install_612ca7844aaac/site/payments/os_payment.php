<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class os_payment
{
	/**
	 * Name of payment method
	 *
	 * @var string
	 */
	public $_name = null;

	/**
	 * Payment Method title
	 * @var string
	 */
	public $_title = null;

	/**
	 * Creditcard payment method ?
	 *
	 * @var string
	 */
	public $_creditCard = false;

	/**
	 * Require card cvv code ?
	 *
	 * @var boolean
	 */
	public $_cardCvv = false;

	/**
	 * Require creditcard type ?
	 *
	 * @var boolean
	 */
	public $_cardType = false;

	/**
	 * Require card holder name ?
	 *
	 * @var boolean
	 */
	public $_cardHolderName = false;

	public $paymentFee = false;

	public function os_payment()
	{
		$this->loadLanguage();
	}

	/**
	 * Getter method for name property
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->_name;
	}

	public function setTitle($title)
	{
		$this->_title = $title;
	}

	public function getTitle()
	{
		return $this->_title;
	}

	/**
	 * Setter method for name property
	 *
	 * @param   string  $value
	 */
	public function setName($value)
	{
		$this->_name = $value;
	}

	/**
	 * Getter method for cardType property
	 *
	 * @return boolean
	 */
	public function getCreditCard()
	{
		if ($this->_creditCard)
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}

	/**
	 * Setter method for creditCard
	 *
	 * @param  $value
	 */
	public function setCreditCard($value)
	{
		$this->_creditCard = $value;
	}

	/**
	 * Setter method for cardCvv
	 *
	 * @return boolean
	 */
	public function getCardCvv()
	{
		if ($this->_cardCvv)
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}

	/**
	 * Setter method for cardCvv
	 *
	 * @param   boolean
	 */
	public function setCardCvv($value)
	{
		$this->_cardCvv = $value;
	}

	/**
	 * Getter method for cardType
	 *
	 * @return boolean
	 */
	public function getCardType()
	{
		if ($this->_cardType)
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}

	/**
	 * Setter method for CardType property
	 *
	 * @param   boolean  $value
	 */
	public function setCardType($value)
	{
		$this->_cardType = $value;
	}

	/**
	 * Getter method for CardHolderName
	 *
	 * @return boolean
	 */
	public function getCardHolderName()
	{
		if ($this->_cardHolderName)
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}

	/**
	 * Setter method for CardHolderName
	 *
	 * @param   boolean  $value
	 */
	public function setCardHolderName($value)
	{
		$this->_cardHolderName = $value;
	}

	/**
	 * Load language file for this payment plugin
	 */
	public function loadLanguage()
	{
		$pluginName = $this->getName();
		$lang       = Factory::getLanguage();
		$tag        = $lang->getTag();
		if (!$tag)
		{
			$tag = 'en-GB';
		}
		$lang->load($pluginName, JPATH_ROOT, $tag);
	}
}