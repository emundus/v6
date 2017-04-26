<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.0.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hikashop_cpanel_main">
	<div class="hikashop_cpanel_title">
		<fieldset>
			<div class="header hikashop_header_title"><h1><?php echo JText::_('CUSTOMER_ACCOUNT');?></h1></div>
		</fieldset>
	</div>
	<div class="hikashop_cpanel" id="hikashopcpanel">
		<div class="hk-row-fluid">
<?php
	$design = $this->config->get('cpanel_design', 'icon');
	if($design == 'icon')
		hikashop_loadJsLib('tooltip');

	foreach($this->buttons as $btn) {
		if($design == 'icon') {
?>
		<div class="icon-wrapper">
			<div class="icon">
				<a href="<?php echo hikashop_level($btn['level']) ? $btn['link'] : '#'; ?>" data-toggle="hk-tooltip" data-title="<?php echo htmlspecialchars('<strong>'.$btn['text'].'</strong><br/>'.$btn['description']); ?>">
					<span class="hkIcon icon-48-<?php echo $btn['image'];?>"></span>
					<span><?php echo $btn['text'];?></span>
				</a>
			</div>
		</div>
<?php
		} else {
			$url = hikashop_level($btn['level']) ? 'onclick="document.location.href=\''.$btn['link'].'\';"' : '';
?>
		<div class="hkc-md-6 hikashop_cpanel_icon_div" <?php echo $url; ?>>
			<a href="<?php echo hikashop_level($btn['level']) ? $btn['link'] : '#'; ?>">
				<div class="hikashop_cpanel_icon_image">
					<span class="hkicon-48 icon-48-<?php echo $btn['image']; ?>" title="<?php echo $btn['text']; ?>"></span>
					<span class="hikashop_cpanel_button_text"><?php echo $btn['text']; ?></span>
				</div>
				<div class="hikashop_cpanel_description"><?php
					echo $btn['description'];
				?></div>
				<div style="clear:both;"></div>
			</a>
		</div>
<?php }
	}
?>
		</div>
	</div>
	<div style="clear:both;"></div>
</div>
