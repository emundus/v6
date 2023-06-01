<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><tr id="zone_namekey-<?php echo $this->row->zone_namekey; ?>" class="row<?php echo $this->k; ?>">
	<td class="hk_center">
		<?php echo @$this->row->zone_name_english; ?>
	</td>
	<td class="hk_center">
		<?php echo @$this->row->zone_name; ?>
	</td>
	<td class="hk_center">
		<?php echo @$this->row->zone_code_2; ?>
	</td>
	<td class="hk_center">
		<?php echo @$this->row->zone_code_3; ?>
	</td>
	<td class="hk_center">
		<?php echo @$this->row->zone_type; ?>
	</td>
	<td>
		<a href="<?php echo hikashop_completeLink('zone&task=edit&cid[]='.@$this->row->zone_id); ?>" target="_blank" title="<?php echo JText::_('HIKA_EDIT'); ?>">
			<i class="fa fa-pen fa-pencil"></i>
		</a>
	</td>
	<td class="hk_center">
		<span class="spanloading">
			<?php echo $this->toggleClass->delete("zone_namekey-".$this->row->zone_namekey,$this->main_namekey.'-'.$this->row->zone_namekey,'zone',true) ?>
		</span>
	</td>
	<td class="hk_center">
		<?php echo @$this->row->zone_id; ?>
	</td>
</tr>
