<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<?php
if(!empty($this->statistics)) {
?>
<div class="hk-container-fluid">
<?php
	foreach($this->statistics_slots as $slot) {
?>
	<div class="hk-row">
<?php
		foreach($this->statistics as $key => $stat) {
			if($stat['slot'] != $slot)
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
?>
</div>
<?php
}

if(!empty($this->widgets)) {
	$itemOnARow = 0;
	if(!HIKASHOP_BACK_RESPONSIVE) {
?>
<table class="adminform" style="width:100%;border-collapse:separate;border-spacing:5px">
<?php
	} else {
		echo '<div class="cpanel-widgets">';
	}

	foreach($this->widgets as $widget) {
		if(empty($widget->widget_params->display))
			continue;

		if(!hikashop_level(2)){
			if(isset($widget->widget_params->content) && in_array($widget->widget_params->content, array('partners', 'map')))
				continue;
			if(!hikashop_level(1) && in_array($widget->widget_params->display,array('gauge','pie')))
				continue;
		}
		if($itemOnARow == 0) {
			echo (!HIKASHOP_BACK_RESPONSIVE) ? '<tr>' :'<div class="row-fluid">';
		}
		$val = preg_replace('#[^a-z0-9]#i', '_', strtoupper($widget->widget_name));
		$trans = JText::_($val);
		if($val != $trans) {
			$widget->widget_name = $trans;
		}
		if(hikashop_level(1) && $this->manage) {
			$widget->widget_name.= '
			<a href="'.hikashop_completeLink('report&task=edit&cid[]='.$widget->widget_id.'&dashboard=true').'">
				<img src="'.HIKASHOP_IMAGES.'edit.png" alt="edit"/>
			</a>';
		}
		if(!HIKASHOP_BACK_RESPONSIVE) {
			echo '<td valign="top" style="border: 1px solid #CCCCCC"><fieldset style="border:0px" class="adminform"><legend>'.$widget->widget_name.'</legend>';
		} else {
			echo '<div class="span4" style="border: 1px solid #CCCCCC;min-height:280px"><fieldset style="border:0px" class="adminform"><legend>'.$widget->widget_name.'</legend>';
		}

		$this->widget =& $widget;
		if($widget->widget_params->display == 'listing') {
			if(empty($widget->widget_params->content_view))
				continue;
			$this->setLayout($widget->widget_params->content_view);
		} elseif(in_array($widget->widget_params->display, array('column', 'line', 'area'))) {
			$this->setLayout('chart');
		} else {
			$this->setLayout($widget->widget_params->display);
		}

		echo $this->loadTemplate();
		echo (!HIKASHOP_BACK_RESPONSIVE) ? '</fieldset></td>' : '</fieldset></div>';

		$itemOnARow++;

		if($itemOnARow == 3) {
			echo (!HIKASHOP_BACK_RESPONSIVE) ? '</tr>' : '</div>';
			$itemOnARow = 0;
		}
	}
	if(!HIKASHOP_BACK_RESPONSIVE) {
?>
</table>
<?php
	} else {
		echo '</div>';
	}
}

$this->setLayout('cpanel');
echo $this->loadTemplate();
