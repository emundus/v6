<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>{address_company}
{address_title} {address_firstname} {address_lastname}
{address_street}
{address_post_code} {address_city} {address_state}
{address_country}
<?php if(!empty($this->address->address_telephone)) echo JText::sprintf('TELEPHONE_IN_ADDRESS','{address_telephone}'); ?>

<?php if(!empty($this->address->address_vat)) echo JText::sprintf('VAT_IN_ADDRESS','{address_vat}'); ?>
