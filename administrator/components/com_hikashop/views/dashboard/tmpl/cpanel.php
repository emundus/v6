<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2016 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="cpanel">
<?php
	$tooltip_class = HIKASHOP_J30 ? 'hasTooltip' : 'hasTip';
	if(HIKASHOP_J30)
		JHtml::_('bootstrap.tooltip');

	foreach($this->buttonList as $btn) {
		if(empty($btn['url']))
			$btn['url'] = hikashop_completeLink($btn['link']);
		if(empty($btn['icon']))
			$btn['icon'] = 'icon-48-' . $btn['image'];

		if($btn['level'] > 0 && !hikashop_level($btn['level']))
			$btn['url'] = 'javascript:void(0);';

		if(false) {
			echo '<li><a href="'.$btn['url'].'"><i class="icon-'.$btn['image'].'"></i> '.$btn['text'].'</a></li>';
		} else {
			$desc = $this->descriptions[$btn['link']];

			if(is_array($desc))
				$desc = implode('<br/>', $desc);
?>
		<div class="icon-wrapper">
			<div class="icon">
				<a class="<?php echo $tooltip_class; ?>" href="<?php echo $btn['url'];?>" title="<?php echo htmlentities($desc, ENT_QUOTES, "UTF-8"); ?>">
					<span class="<?php echo $btn['icon'];?>" style="background-repeat:no-repeat;background-position:center;height:48px;padding:10px 0;"></span>
					<span><?php echo $btn['text'];?></span>
				</a>
			</div>
		</div>
<?php
		}
	}
?>
	<div style="clear:both"></div>
</div>
