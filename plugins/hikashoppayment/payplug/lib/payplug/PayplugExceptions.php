<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

class PayplugException extends Exception
{
}

class InvalidCredentialsException extends PayplugException
{
}

class InvalidSignatureException extends PayplugException
{
}

class MalformedURLException extends PayplugException
{
}

class NetworkException extends PayplugException
{
}

class ParametersNotSetException extends PayplugException
{
}

class MissingRequiredParameterException extends PayplugException
{
}

