<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.0.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="page-languages">

<div class="hikashop_backend_tile_edition">
	<div class="hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('LANGUAGES'); ?></div>
<table class="hk_config_table table" style="width:100%">

	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('multi_language_edit');?>><?php echo JText::_('MULTI_LANGUAGE_EDIT'); ?></td>
		<td><?php
	if(hikashop_level(1)) {
		$translationHelper = hikashop_get('helper.translation');
		if($translationHelper->isMulti(true)) {
			$update = hikashop_get('helper.update');
			$update->addJoomfishElements(false);
			echo JHTML::_('hikaselect.booleanlist', "config[multi_language_edit]" , '', $this->config->get('multi_language_edit'));
		} else {
			echo JText::_('INSTALL_JOOMFISH');
		}
	} else {
		echo hikashop_getUpgradeLink('essential');
		?><input type="hidden" value="0" name="config[multi_language_edit]" /><?php
	}
		?></td>
	</tr>
<?php if(!$this->config->get('default_translation_publish', 1) && hikashop_level(1)) { ?>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('default_translation_publish');?>><?php echo JText::_('DEFAULT_TRANSLATION_PUBLISH'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', "config[default_translation_publish]" , '',$this->config->get('default_translation_publish',1));
		?></td>
	</tr>
<?php if(hikashop_level(9)){ ?>
	<tr>
		<td class="hk_tbl_key"><?php echo JText::_('MUTLILANGUAGE_INTERFACE_DISPLAY'); ?></td>
		<td><?php
			echo $this->multilang->display("config[multilang_display]" , $this->config->get('multilang_display','tabs') );
		?></td>
	</tr>
<?php } ?>
<?php } ?>
</table>
<table class="adminlist table table-striped" cellpadding="1">
	<thead>
		<tr>
			<th class="title titlenum"><?php
				echo JText::_('HIKA_NUM');
			?></th>
			<th class="title titletoggle"><?php
				echo JText::_('HIKA_EDIT');
			?></th>
			<th class="title"><?php
				echo JText::_('HIKA_NAME');
			?></th>
			<th class="title titletoggle"><?php
				echo JText::_('ID');
			?></th>
		</tr>
	</thead>
	<tbody>
<?php
	$k = 0;
	for($i = 0,$a = count($this->languages);$i<$a;$i++){
		$row =& $this->languages[$i];
?>
		<tr class="row<?php echo $k; ?>">
			<td class="hk_center">
			<?php echo $i+1; ?>
			</td>
			<td  class="hk_center">
				<?php if($this->manage) echo $row->edit; ?>
			</td>
			<td class="hk_center">
				<?php echo $row->name; ?>
			</td>
			<td class="hk_center">
				<?php echo $row->language; ?>
			</td>
		</tr>
<?php
		$k = 1-$k;
	}
?>
	</tbody>
</table>
	</div></div>
</div>

</div>
