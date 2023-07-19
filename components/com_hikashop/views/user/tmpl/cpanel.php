<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hikashop_cpanel_main_interface">
<?php
	if(!empty($this->title)) {
?>
	<div class="header hikashop_header_title">
		<h1><?php echo $this->title; ?></h1>
	</div>
	<div style="clear:both;"></div>
<?php
	}

	$legacy = (int)$this->config->get('cpanel_legacy', false);
	if($legacy) {
?>
	<div class="hikashop_cpanel" id="hikashopcpanel">
		<div class="hk-row-fluid">
<?php
		foreach($this->buttons as $name => $btn) {
?>
		<div class="icon-wrapper hikashop_cpanel_<?php echo $name; ?>_div">
			<div class="icon">
				<a href="<?php echo hikashop_level($btn['level']) ? $btn['link'] : '#'; ?>" data-toggle="hk-tooltip" data-title="<?php echo htmlspecialchars('<strong>'.$btn['text'].'</strong><br/>'.$btn['description']); ?>">
					<span class="hkIcon icon-48-<?php echo $btn['image'];?>"></span>
					<span><?php echo $btn['text'];?></span>
				</a>
			</div>
		</div>
<?php
		}
?>
		</div>
	</div>
	<div style="clear:both;"></div>
</div>
<?php
		return;
	}
?>
	<div class="hk-row-fluid hikashop_dashboard" id="hikashop_dashboard">
		<div class="hika_cpanel_side_bar hkc-md-3">
<?php if(!empty($this->extraData->topLeft)) { echo implode("\r\n", $this->extraData->topLeft); } ?>
	<div class="hika_cpanel_icons">
<?php

	$flag = false;
	foreach($this->buttons as $name => $btn) {
		$data = isset($btn['counter']) ? $btn['counter'] : false;

?>
		<a class="hika_cpanel_icon hikashop_cpanel_<?php echo $name; ?>_div" href="<?php echo hikashop_level($btn['level']) ? $btn['link'] : '#'; ?>" data-toggle="hk-tooltip" data-title="<?php echo htmlspecialchars('<strong>'.$btn['text'].'</strong><br/>'.$btn['description']); ?>">
<?php
			if (!empty($btn['fontawesome'])) {
?>
			<span class="hk-icon fa-stack fa-2x <?php echo $btn['image']; ?>"><?php echo $btn['fontawesome']; ?></span>
<?php
			} else {
?>
			<span class="hkicon-48 icon-48-<?php echo $btn['image']; ?>" title="<?php echo $btn['text']; ?>"></span>
<?php
			}
?>
			<span class="hikashop_cpanel_button_text"><?php echo $btn['text'];?></span>
<?php
			if (($data != "") && ($sub_menu == true)) {
?>
			<span class="hikashop_cpanel_data"><?php echo $data; ?></span>
<?php
			}
?>
		</a>
<?php
	}
?>
	</div>
<?php if(!empty($this->extraData->bottomLeft)) { echo implode("\r\n", $this->extraData->bottomLeft); } ?>
		</div>
		<div class="hika_cpanel_main_data hkc-md-9">
<?php
	if(!empty($this->extraData->topMain)) { echo implode("\r\n", $this->extraData->topMain); }
	echo $this->loadTemplate('orders');
	if(!empty($this->extraData->bottomMain)) { echo implode("\r\n", $this->extraData->bottomMain); }
?>
		</div>
	</div>
</div>
