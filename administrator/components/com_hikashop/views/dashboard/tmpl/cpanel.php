<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="cpanel">
<?php
	hikashop_loadJsLib('tooltip');

	foreach($this->buttonList as $btn) {
		if(empty($btn['url']))
			$btn['url'] = hikashop_completeLink($btn['link']);
		if(empty($btn['icon']))
			$btn['icon'] = $btn['image'];

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
				<a href="<?php echo $btn['url'];?>" data-toggle="hk-tooltip" data-title="<?php echo htmlspecialchars($desc, ENT_COMPAT, 'UTF-8'); ?>">
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
