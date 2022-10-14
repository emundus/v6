<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.4.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
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
		ksort($this->aclcats);
		foreach($this->aclcats as $category => $actions){ ?>
		<tr>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
<td width="185" class="key" valign="top">
<?php } else { ?>
<td>
<?php } ?>
				<?php
				$trans='';

				if(!empty($this->acltrans[$category])){
					 $trans = JText::_(strtoupper($this->acltrans[$category]));
					 if($trans == strtoupper($this->acltrans[$category])){
					 	$trans = '';
					 }
				}
				if(empty($trans)) $trans = JText::_('HIKA_'.strtoupper($category));
				if($trans == 'HIKA_'.strtoupper($category)) $trans = JText::_(strtoupper($category));

				echo $trans;
				?>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
			</td>
			<td>
<?php } ?>
				<?php echo $this->acltable->display($category,$actions)?>
			</td>
		</tr>
		<?php } ?>
	</table>
</div>
