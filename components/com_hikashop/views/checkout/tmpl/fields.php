<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php if(hikashop_level(2) && !empty($this->extraFields['order'])){ ?>
<div  id="hikashop_checkout_additional_info" class="hikashop_checkout_additional_info">
	<fieldset class="input">
		<legend><?php echo JText::_('ADDITIONAL_INFORMATION');?></legend>
		<table cellpadding="0" cellspacing="0" border="0" class="hikashop_contentpane">
<?php
	if(!empty($this->extraFields['order'])){
		hikaInput::get()->set('hikashop_check_order',1);
		$this->setLayout('custom_fields');
		$this->type = 'order';
		echo $this->loadTemplate();
	}
?>
		</table>
	</fieldset>
</div>
<div style="clear:both"></div>
<?php } ?>
