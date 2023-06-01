<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
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
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('translated_aliases');?>><?php echo JText::_('SUPPORT_TRANSLATED_ALIASES_IN_URLS'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', "config[translated_aliases]" , '',$this->config->get('translated_aliases',0));
		?></td>
	</tr>
<?php
	if($this->config->get('multi_language_edit')) {
?>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('multi_language_edit_xy');?>><?php echo JText::_('MULTI_LANGUAGE_EDIT_XY'); ?></td>
		<td>
			<input class="inputbox" type="text" name="config[multi_language_edit_x]" value="<?php echo $this->config->get('multi_language_edit_x', 760); ?>" />
			px <i class="fas fa-times fa-2x"></i>
			<input class="inputbox" type="text" name="config[multi_language_edit_y]" value="<?php echo $this->config->get('multi_language_edit_y',  480); ?>" />
			px
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('non_latin_translation_keys');?>><?php echo JText::_('NON_LATIN_TRANSLATION_KEYS'); ?></td>
		<td>
		<?php
			echo JHTML::_('hikaselect.booleanlist', "config[non_latin_translation_keys]" , '',$this->config->get('non_latin_translation_keys',0));
		?>
		</td>
	</tr>
<?php 
	}
	if(!$this->config->get('default_translation_publish', 1) && hikashop_level(1)) { ?>
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
			<th class="title titlenum hk_center"><?php
				echo JText::_('HIKA_NUM');
			?></th>
			<th class="title titletoggle hk_center"><?php
				echo JText::_('HIKA_EDIT');
			?></th>
			<th class="title titletoggle hk_center"><?php
				echo JText::_('HIKASHOP_CHECKOUT_STATUS');
			?></th>
			<th class="title hk_center"><?php
				echo JText::_('HIKA_NAME');
			?></th>
			<th class="title titletoggle hk_center"><?php
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
			<td class="hk_tbl_key hk_center" data-toggle="hk-tooltip" data-title="<?php echo $row->edit_tooltip ?>">
				<?php if($this->manage) echo $row->edit; ?>
			</td>
			<td class="hk_tbl_key hk_center" data-toggle="hk-tooltip" data-title="<?php echo $row->status_tooltip ?>">
				<?php echo $row->status; ?>
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
<table style="margin-bottom: 8px;">
	<tr>
		<td style="width: 25%; font-weight: bold; padding: 8px;">
			<?php echo JText::_('INSTALL_MISSING_LANGUAGE_IN_JOOMLA'); ?>
		</td>
	</tr>
</table>
	</div></div>
</div>

</div>
