<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<table class="adminlist table table-striped table-hover" cellpadding="1">
	<thead>
		<tr>
			<th class="title titlenum"><?php
				echo JText::_('HIKA_NUM');
			?></th>
			<th class="title"><?php
				echo JText::_('HIKA_NAME');
			?></th>
<?php
	if(!empty($this->currencies)) {
		foreach($this->currencies as $currency) {
?>			<th class="title"><?php
				echo @$currency->currency_code;
			?></th>
<?php
		}
	}
?>
			<th class="title titletoggle"><?php
				echo JText::_('HIKA_ENABLED');
			?></th>
			<th class="title titleid"><?php
				echo JText::_('ID');
			?></th>
		</tr>
	</thead>
	<tbody>
<?php
$k = 0;

if(!HIKASHOP_J30) {
	$icon_yes = '<img src="templates/hathor/images/admin/tick.png" alt="Y"/>';
	$icon_no = '<img src="templates/hathor/images/admin/publish_x.png" alt=""/>';
} else {
	$icon_yes = '<span class="icon-publish"></span>';
	$icon_no = '<span class="icon-unpublish"></span>';
}

foreach($this->plugins as $i => &$row) {
	$publishedid = 'enabled-'.$row->id;
?>
		<tr class="row<?php echo $k; ?>">
			<td class="hk_center"><?php
				echo $i+1
			?></td>
			<td><?php
				if($this->manage){
					?><a href="<?php echo hikashop_completeLink('plugins&task=edit&name='.$row->element.'&plugin_type='.$this->plugin_type.'&subtask=edit');?>"><?php
				}
				echo JText::_($row->name);
				if($this->manage){
					?></a><?php
				}
			?></td>
<?php
	if(!empty($this->currencies)) {
		foreach($this->currencies as $currency) {
?>			<td class="hk_center"><?php
				if(empty($row->accepted_currencies) || in_array($currency->currency_code, $row->accepted_currencies))
					echo $icon_yes;
				else
					echo $icon_no;
			?></td>
<?php
		}
	}
?>
			<td class="hk_center">
				<span id="<?php echo $publishedid ?>" class="loading"><?php
					if($this->manage){
						echo $this->toggleClass->toggle($publishedid,$row->published,'plugins');
					}else{
						$this->toggleClass->display('activate',$row->published);
					}
				?></span>
			</td>
			<td class="hk_center"><?php
				echo $row->id;
			?></td>
		</tr>
<?php
	$k = 1-$k;
}
?>
	</tbody>
</table>
