<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
echo $this->leftmenu(
	'advanced',
	array(
		'#advanced_advanced' => JText::_('HIKA_ADVANCED_SETTINGS'),
		'#advanced_legacy' => JText::_('HIKASHOP_LEGACY_SETTINGS')
	)
);
?>
<div id="page-advanced" class="rightconfig-container <?php if(HIKASHOP_BACK_RESPONSIVE) echo 'rightconfig-container-j30';?>">

<!-- Advanced -->
<div id="advanced_advanced" class="hikashop_backend_tile_edition">
	<div class="hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('HIKA_ADVANCED_SETTINGS'); ?></div>
<table class="hk_config_table table" style="width:100%">

	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('display_view_files');?>><?php echo JText::_('DISPLAY_VIEW_FILES'); ?></td>
		<td><?php
		$arr = array(
			JHTML::_('select.option', '0', JText::_('HIKASHOP_NO') ),
			JHTML::_('select.option', '1',  JText::_('DISPLAY_FRONTCOMP') ),
			JHTML::_('select.option', '2',  JText::_('HIKA_ALL') ),
		);
		echo JHTML::_('hikaselect.genericlist', $arr, "config[display_view_files]" , 'class="custom-select"', 'value', 'text',$this->config->get('display_view_files', 0) );?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('cart_retaining_period');?>><?php echo JText::_('CART_RETAINING_PERIOD'); ?></td>
		<td><?php
			echo $this->delayTypeRetaining->display('config[cart_retaining_period]', $this->config->get('cart_retaining_period', 2592000));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('cart_retaining_period_check_frequency');?>><?php echo JText::_('CART_RETAINING_PERIOD_CHECK_FREQUENCY'); ?></td>
		<td><?php
			echo $this->delayTypeCarts->display('config[cart_retaining_period_check_frequency]', $this->config->get('cart_retaining_period_check_frequency', 1200));
			?><br/><?php
			echo JText::sprintf('LAST_CHECK', hikashop_getDate($this->config->get('cart_retaining_period_checked')));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('use_trash');?>><?php echo JText::_('HIKA_USE_TRASH'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[use_trash]', '', $this->config->get('use_trash', 0));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('volume_symbols');?>><?php echo JText::_('DIMENSION_SYMBOLS'); ?></td>
		<td>
			<input class="inputbox" type="text" name="config[volume_symbols]" value="<?php echo $this->config->get('volume_symbols'); ?>">
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('weight_symbols');?>><?php echo JText::_('WEIGHT_SYMBOLS'); ?></td>
		<td>
			<input class="inputbox" type="text" name="config[weight_symbols]" value="<?php echo $this->config->get('weight_symbols'); ?>">
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('editor');?>><?php echo JText::_('HIKA_EDITOR'); ?></td>
		<td><?php
			echo $this->editorType->display('config[editor]', $this->config->get('editor'));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('description_format');?>><?php echo JText::_('DESCRIPTION_DEFAULT_FORMAT'); ?></td>
		<td><?php
			echo $this->contentparserType->display('config[default_description_type]', $this->config->get('default_description_type'));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('readmore');?>><?php echo JText::_('READ_MORE'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', "config[readmore]",'',$this->config->get('readmore'));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('deactivate_buffering_and_compression');?>><?php echo JText::_('DEACTIVATE_BUFFERING_AND_COMPRESSION'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[deactivate_buffering_and_compression]','',$this->config->get('deactivate_buffering_and_compression',0));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('redirect_post');?>><?php echo JText::_('REDIRECT_POST_MODE'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[redirect_post]','',$this->config->get('redirect_post',0));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('partner_id');?>><?php echo JText::_('AFFILIATE');?></td>
		<td>
			<input name="config[partner_id]" type="text" value="<?php echo $this->config->get('partner_id')?>" />
		</td>
	</tr>
<?php
?>

</table>
	</div></div>
</div>

<!-- MAIN - LEGACY -->
<div id="advanced_legacy" class="hikashop_backend_tile_edition">
	<div class="hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('HIKASHOP_LEGACY_SETTINGS'); ?></div>
<table class="hk_config_table table" style="width:100%">

	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('bootstrap_design');?>><?php echo JText::_('USE_BOOTSTRAP_ON_FRONT'); ?></td>
		<td><?php
			echo JHtml::_('hikaselect.booleanlist', 'config[bootstrap_design]', '', $this->config->get('bootstrap_design', HIKASHOP_J30));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('variant_default_publish');?>><?php echo JText::_('DEFAULT_VARIANT_PUBLISH'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', "config[variant_default_publish]" , '', $this->config->get('variant_default_publish', 1));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('dashboard_legacy_mode');?>><?php echo JText::_('DASHBOARD_LEGACY'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[legacy_widgets]', '', $this->config->get('legacy_widgets', 1));
		?></td>
	</tr>
<?php if(!HIKASHOP_J40) { ?>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('carousel_legacy_mode');?>><?php echo JText::_('CAROUSEL_LEGACY'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[carousel_legacy]', '', $this->config->get('carousel_legacy', 1));
		?></td>
	</tr>
<?php } ?>
<?php if($this->config->get('server_current_url_mode', '0') != '0'){ ?>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('server_current_url_mode');?>><?php echo JText::_('SERVER_CURRENT_URL_MODE'); ?></td>
		<td><?php
		$arr = array(
			JHTML::_('select.option', '0', JText::_('HIKA_AUTOMATIC') ),
			JHTML::_('select.option', 'REDIRECT_URL',  JText::_('REDIRECT_URL') ),
			JHTML::_('select.option', 'REQUEST_URI',  JText::_('REQUEST_URI') ),
		);
		echo JHTML::_('hikaselect.genericlist', $arr, "config[server_current_url_mode]" , 'class="custom-select"', 'value', 'text',$this->config->get('server_current_url_mode','0') );?></td>
	</tr>
<?php } ?>
<?php if($this->config->get('checkout_legacy', 0)){ ?>
	<tr>
		<td colspan="2"><h4><?php echo JText::_('CHECKOUT_LEGACY_MODE'); ?></h4></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('auto_select_default');?>><?php echo JText::_('AUTO_SELECT_DEFAULT_SHIPPING_AND_PAYMENT'); ?></td>
		<td><?php
			echo $this->auto_select->display('config[auto_select_default]', $this->config->get('auto_select_default',2));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('auto_submit_methods');?>><?php echo JText::_('AUTO_SUBMIT_SHIPPING_AND_PAYMENT_SELECTION'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[auto_submit_methods]', '', $this->config->get('auto_submit_methods', 1));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('store_open_hour');?>><?php echo JText::_('BUSINESS_HOURS'); ?></td>
		<td><?php
	if(hikashop_level(1)) {
		$hours = array();
		for($i = 0; $i < 24; $i++) $hours[] = JHTML::_('select.option', $i, $i);
		$minutes = array();
		for($i = 0; $i < 60; $i++) $minutes[] = JHTML::_('select.option', $i,$i);
		echo JText::_('OPENS_AT');
		echo JHTML::_('select.genericlist', $hours, 'config[store_open_hour]', 'class="custom-select" size="1"', 'value', 'text', $this->config->get('store_open_hour', 0)).
			JText::_('HOURS');
		echo JHTML::_('select.genericlist', $minutes, "config[store_open_minute]", 'class="custom-select" size="1"', 'value', 'text', $this->config->get('store_open_minute', 0)).
			JText::_('HIKA_MINUTES').'<br/>';
		echo JText::_('CLOSES_AT');
		echo JHTML::_('select.genericlist', $hours, 'config[store_close_hour]', 'class="custom-select" size="1"', 'value', 'text', $this->config->get('store_close_hour', 0)).
			JText::_('HOURS');
		echo JHTML::_('select.genericlist', $minutes, 'config[store_close_minute]', 'class="custom-select" size="1"', 'value', 'text', $this->config->get('store_close_minute', 0)).
			JText::_('HIKA_MINUTES');
	}else{
		echo '<small style="color:red">'.JText::_('ONLY_COMMERCIAL').'</small>';
	}
		?></td>
	</tr>
    <tr>
        <td class="hk_tbl_key"<?php echo $this->docTip('shipping_address_same_checkbox');?>><?php echo JText::_('SHOW_SHIPPING_SAME_ADDRESS_CHECKBOX'); ?></td>
        <td><?php
            echo JHTML::_('hikaselect.booleanlist', 'config[shipping_address_same_checkbox]', '', $this->config->get('shipping_address_same_checkbox', 1));
        ?></td>
    </tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('checkout_convert_cart');?>><?php echo JText::_('CHECKOUT_CONVERT_CART'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[checkout_convert_cart]', '', $this->config->get('checkout_convert_cart', 1));
		?></td>
	</tr>
<?php } ?>
</table>
	</div></div>
</div>

</div>
