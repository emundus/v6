<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
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
