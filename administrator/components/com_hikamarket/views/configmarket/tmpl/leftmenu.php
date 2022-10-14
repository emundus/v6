<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="leftmenu-container <?php if(HIKASHOP_BACK_RESPONSIVE) echo 'leftmenu-container-j30';?>">
	<div <?php if(!HIKASHOP_BACK_RESPONSIVE) echo 'class="config-menu"';?> id="menu_<?php echo $this->menuname; ?>">
		<a id="menu-scrolltop-<?php echo $this->menuname; ?>" href="#" onclick="window.scrollTo(0, 0);" class="menu-scrolltop" style="float: right; margin:12px 2px 0px 2px;">
			<span class="scrollTop_img" style="padding: 11px 18px;"></span>
		</a>
		<ul <?php if(HIKASHOP_BACK_RESPONSIVE) echo 'class="hika-navbar-ul" data-spy="affix" data-offset-top="60"';?>>
<?php
	foreach($this->menudata as $href => $name) {
?>			<li><a href="<?php echo $href; ?>"><?php echo $name; ?><i class="icon-chevron-right"></i></a></li>
<?php
	}
?>
		</ul>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
		<a id="menu-save-button-<?php echo $this->menuname; ?>" class="menu-save-button" onclick="window.hikashop.submitform('apply', 'adminForm'); return false;" href="#" style="float: right; margin:0px 16px 6px 0px;">
			<span class="icon-32-apply" style="padding: 12px 16px;"> </span><?php echo JText::_('JAPPLY'); ?>
		</a>
<?php } ?>
	</div>
</div>
