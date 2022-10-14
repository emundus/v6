<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<div class="hk-container-fluid">
<?php
if(!empty($this->statistics_slots)) {
	foreach($this->statistics_slots as $slot) {
?>
	<div class="hk-row">
<?php
		foreach($this->statistics as $key => $stat) {
			if($stat['slot'] != $slot)
				continue;
			if(isset($stat['published']) && empty($stat['published']))
				continue;
			$class = 'hkc-sm-12';
			if(isset($stat['class']))
				$class = $stat['class'];
?>
		<div id="hikashop_dashboard_stat_<?php echo $key; ?>" class="<?php echo $class; ?>"><?php
			echo $this->statisticsClass->display($stat);
		?></div>
<?php
		}
?>
	</div>
<?php
	}
}
?>
</div>
<div id="cpanel">
<?php foreach($this->buttons as $btn) { ?>
	<div class="icon-wrapper">
		<div class="icon">
			<a href="<?php echo $btn['url'];?>">
				<span class="<?php echo $btn['icon'];?>" style="background-repeat:no-repeat;background-position:center;height:48px;padding:10px 0;"></span>
				<span><?php echo $btn['name'];?></span>
			</a>
		</div>
	</div>
<?php } ?>
	<div style="clear:both"></div>
</div>
