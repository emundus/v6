<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.2.2
 * @author	hikashop.com
 * @copyright	(C) 2010-2019 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
namespace Payplug\Exception;

abstract class PayplugException extends \Exception
{
    public function __toString()
    {
        return get_class($this) . ": [{$this->code}]: {$this->message}";
    }
}
