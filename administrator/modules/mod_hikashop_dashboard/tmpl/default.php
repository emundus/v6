<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hk-container-fluid">
<?php
	foreach($statistics_slots as $slot) {
?>
	<div class="hk-row">
<?php
		foreach($statistics as $key => $stat) {
			if($stat['slot'] != $slot)
				continue;
			$class = 'hkc-sm-12';
			if(isset($stat['class']))
				$class = $stat['class'];
?>
		<div id="hikashop_dashboard_stat_<?php echo $key; ?>" class="<?php echo $class; ?>"><?php
			echo $statisticsClass->display($stat);
		?></div>
<?php
		}
?>
	</div>
<?php
	}
?>
</div>
