<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="leftmenu-container <?php if(HIKASHOP_BACK_RESPONSIVE) echo 'leftmenu-container-j30';?>">
	<div <?php if(!HIKASHOP_BACK_RESPONSIVE) echo 'class="config-menu"';?> id="menu_<?php echo $this->menuname; ?>">
		<a id="menu-scrolltop-<?php echo $this->menuname; ?>" href="#" onclick="window.scrollTo(0, 0);" class="menu-scrolltop" style="float: right; margin:12px 2px 0px 2px;">
			<span class="scrollTop_img" style="padding: 11px 18px;"></span>
		</a>
		<ul <?php if(HIKASHOP_BACK_RESPONSIVE) echo 'class="hika-navbar-ul"';?>>
<?php
	foreach($this->menudata as $href => $name) {
?>			<li>
				<a href="<?php echo $href; ?>" data-section="<?php echo substr($href, 1); ?>" onclick="localStorage.setItem('hikashop_backend_config_last_section', this.getAttribute('data-section')); return true;">
					<?php echo $name; ?><i class="icon-chevron-right"></i>
				</a>
				<div style="clear:left;">
				</div>
			</li>
<?php
	}
	if(HIKASHOP_BACK_RESPONSIVE){
		?>
		<li id="responsive_menu_scrolltop_li_<?php echo $this->menuname; ?>">
			<a style="text-align:center;" href="#" onclick="localStorage.removeItem('hikashop_backend_config_last_section'); window.scrollTo(0, 0);">
				<i class="fa fa-chevron-circle-up"></i>
			</a>
			<div style="clear:left;"></div>
		</li>
		<?php
	}
?>
		</ul>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
		<a id="menu-save-button-<?php echo $this->menuname; ?>" class="menu-save-button" onclick="window.hikashop.submitform('apply', 'adminForm'); return false;" href="#" style="float: right; margin:0px 4px 2px 0px;">
			<span class="menuSave_img" style="padding: 12px 16px;"> </span>
		</a>
<?php } ?>
	</div>
</div>
