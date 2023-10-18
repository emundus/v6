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

echo JText::sprintf('X_IMPORT_DESC','Mijoshop','Mijoshop').' :<br/>';
$functions = array('TAXATIONS','HIKA_CATEGORIES','PRODUCTS','PRICES','USERS','ORDERS','DOWNLOADS','FILES','HIKA_IMAGES','DISCOUNTS','COUPONS');
foreach($functions as $k => $v){
	echo '<br/>  - '.JText::_($v);
}
?>
<table class="admintable table" cellspacing="1">
<tr>
	<td class="key" >
		<?php echo JText::_('MIJO_IMPORT_CURRENCIES'); ?>
	</td>
	<td>
		<?php echo JHTML::_('hikaselect.booleanlist', 'import_currencies','',hikaInput::get()->getInt('import_currencies','0')); ?>
	</td>
</tr>
</table>

<?php

