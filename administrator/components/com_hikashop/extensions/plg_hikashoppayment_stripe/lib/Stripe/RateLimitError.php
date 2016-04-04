<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2016 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

class Stripe_RateLimitError extends Stripe_InvalidRequestError
{
  public function __construct($message, $param, $httpStatus=null,
	  $httpBody=null, $jsonBody=null
  )
  {
	parent::__construct($message, $httpStatus, $httpBody, $jsonBody);
  }
}
