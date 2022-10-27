<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hikamarket_cpanel_main" id="hikamarket_cpanel_main">
	<div class="hikamarket_cpanel_title header" id="hikamarket_cpanel_title">
		<h1><?php echo JText::_('VENDOR_ACCOUNT');?></h1>
	</div>
	<div class="hikamarketcpanel" id="hikamarketcpanel">
<?php
if(!empty($this->multiple_vendor)) {
?>
		<div style="float:right">
<form id="hikamarket_vendor_switcher" name="hikamarket_vendor_switcher" method="post" action="<?php echo hikamarket::completeLink('vendor&task=switchvendor'.$this->url_itemid); ?>" style="margin:0">
	<?php
		echo JHTML::_('select.genericlist', $this->multiple_vendor, 'vendor_id', ' style="margin:0" onchange="this.form.submit();"', 'value', 'text', $this->vendor->vendor_id);
	?>
	<?php echo JHTML::_('form.token'); ?>
</form>
		</div>
<?php
}
?>
		<h2><?php echo $this->vendor->vendor_name; ?></h2>
		<div style="clear:right"></div>
<?php
$legacy = (int)$this->shopConfig->get('cpanel_legacy', false);
if($legacy) {
?>
	<div class="hikamarket_cpanel">
<?php
		foreach($this->buttons as $btn) {
?>
	<div class="icon-wrapper">
		<div class="icon">
			<a href="<?php echo $btn['url'];?>">
				<span class="<?php echo $btn['icon'];?>" style="background-repeat:no-repeat;background-position:center;height:48px;padding:10px 0;"></span>
				<span><?php echo $btn['name'];?></span>
			</a>
		</div>
	</div>
<?php
		}
?>
	</div>
	<div style="clear:both;"></div>
<?php
} else {
?>
	<div class="hk-row-fluid hikashop_dashboard" id="hikashop_dashboard">
		<div class="hika_cpanel_side_bar hkc-md-3">
<?php if(!empty($this->extraData->topLeft)) { echo implode("\r\n", $this->extraData->topLeft); } ?>
	<div class="hika_cpanel_icons">
<?php
	foreach($this->buttons as $btnName => $btn) {
		if(empty($btn))
			continue;

?>
		<a class="hika_cpanel_icon" href="<?php echo $btn['url']; ?>">
<?php
		if (!empty($btn['fa'])) {
			if(substr($btn['icon'], 0, 9) == 'iconM-48-')
				$btn['icon'] = substr($btn['icon'], 9);

			if(is_string($btn['fa'])) {
?>
			<span class="hk-icon fa-stack fa-2x hk-icon-<?php echo $btn['icon']; ?>">
				<i class="<?php echo $btn['fa'];?> fa-stack-2x"></i>
			</span>
<?php
			} else {
?>
			<span class="hk-icon fa-stack fa-2x hk-icon-<?php echo $btn['icon']; ?>"><?php
				echo implode('', $btn['fa']);
			?></span>
<?php
			}
		} else {
?>
			<span class="hkicon-48 <?php echo $btn['icon']; ?>" style="background-repeat:no-repeat;background-position:center;height:48px;padding:0;"></span>
<?php
		}
?>
			<span class="hikashop_cpanel_button_text"><?php echo $btn['name'];?></span>
		</a>
<?php
	}
?>
	</div>
<?php if(!empty($this->extraData->bottomLeft)) { echo implode("\r\n", $this->extraData->bottomLeft); } ?>
		</div>
		<div class="hika_cpanel_main_data hkc-md-9">
<?php
}

if(!empty($this->statistics)) {
?>
<div class="hikamarket_cpanel_statistics_top hk-row-fluid">
<?php
	$s = 0;
	foreach($this->statistics as $stat) {
		if(empty($stat['published']))
			continue;

		$key = $stat['key'];
		if(empty($stat['container']) || !in_array($stat['container'], array(3,4,6,8,9,12)))
			$stat['container'] = 12;

		if($s < 12 && ($s + (int)$stat['container']) > 12)
			echo '<div class="clearfix"></div>';
?>
		<div class="hkc-md-<?php echo $stat['container']; ?>">
			<div class="hikamarket_panel hikamarket_panel_stats">
				<div class="hikamarket_panel_heading"><?php echo $stat['label']; ?></div>
				<div id="hikamarket_dashboard_stat_<?php echo $key; ?>" class="hikamarket_panel_body"><?php
					echo $this->statisticsClass->display($stat);
				?></div>
			</div>
		</div>
<?php
		if($stat['container'] == 12) {
			$s = 0;
			continue;
		}
		if(($s + (int)$stat['container']) == 12)
			echo '<div class="clearfix"></div>';
		$s += (int)$stat['container'];
		if($s >= 12) $s = 0;
	}

?>
</div>
<?php
}

if(!$legacy) {
?>
	</div>
</div>
<?php
}
?>
	</div>
</div>
<div class="clear_both"></div>
