<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="page-acl">
<table class="admintable" cellspacing="1">
	<tr>
		<td class="key" >
			<?php echo JText::_('INHERIT_PARENT_GROUP_ACCESS'); ?>
		</td>
		<td>
			<?php echo JHTML::_('hikaselect.booleanlist', "config[inherit_parent_group_access]" , '', $this->config->get('inherit_parent_group_access')); ?>
		</td>
	</tr>
</table>
<br style="font-size:1px;" />
	<table class="admintable table" cellspacing="1">
		<?php
		foreach($this->acl_translations as $category => $trans){ ?>
		<tr>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
<td width="185" class="key" valign="top">
<?php } else { ?>
<td>
<?php } ?>
				<?php
				echo $trans;
				?>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
			</td>
			<td>
<?php } ?>
				<?php echo $this->acltable->display($category,$this->aclcats[$category])?>
			</td>
		</tr>
		<?php } ?>
	</table>
</div>
