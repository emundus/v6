<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.6.2
 * @author	hikashop.com
 * @copyright	(C) 2010-2022 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

namespace PayPalHttp\Serializer;

class FormPart
{
    private $value;
    private $headers;

    public function __construct($value, $headers)
    {
        $this->value = $value;
        $this->headers = array_merge([], $headers);
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getHeaders()
    {
        return $this->headers;
    }
}
