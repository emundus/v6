<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$opt = '';
$dateFormat = '';
if(!isset($this->widget->widget_id)) {
	$id = 'preview';
} else {
	$id = $this->widget->widget_id;
}

$chartType = '';
switch($this->widget->widget_params->display) {
	case 'line':
		$chartType = 'LineChart';
		break;
	case 'column':
		$chartType = 'ColumnChart';
		break;
	case 'area':
		$chartType = 'AreaChart';
		break;
}

if(isset($this->widget->widget_params->period_compare)) {
	switch($this->widget->widget_params->period_compare) {
		case 'last_period':
			$opt = "hAxis: { format: 'MMM, dd' }";
			break;
		case 'last_year':
			$opt = "hAxis: { format: 'MMM, dd' }";
			$dateFormat = "var date_formatter = new google.visualization.DateFormat({pattern: 'MMM, d'});\r\n".
						"date_formatter.format(dataTable, 0); ";
			break;
		case 'every_year':
			$date = ' - ';
			break;
	}
}

if($this->widget->widget_params->date_group == '%H %j %Y') {
	$opt = "hAxis: { format: 'yyyy, MMM d - H:00' }";
	$dateFormat = "var date_formatter = new google.visualization.DateFormat({pattern: 'yyyy, MMM d - H:00'});\r\n".
				"date_formatter.format(dataTable, 0);";
}

$name=$this->widget->widget_params->content;
if(JText::_($name) != $name) {
	$name = JText::_($name);
}elseif(JText::_('HIKA_'.$name) != $name) {
	$name = JText::_('HIKA_'.$name);
}
$date = false;
if(!empty($this->widget->widget_params->period_compare) && $this->widget->widget_params->period_compare != 'none') {
	$date = true;
}
?>
<script language="JavaScript" type="text/javascript">
function drawChart() {
	var dataTable = new google.visualization.DataTable();
<?php
	if($date) {
		echo "dataTable.addColumn('date');";
	}else {
		echo "dataTable.addColumn('string');";
	}
	$dates = array();
	$types = array();
	$i= 0;
	$a = 1;

	foreach($this->widget->elements as $oneResult){
		if(empty($oneResult->type)){ continue; }
		if(!isset($dates[$oneResult->calculated_date])){
			$dates[$oneResult->calculated_date] = $i;
			$i++;
			echo "dataTable.addRows(1);"."\n";

			$date_day = $oneResult->day;
			if(isset($this->widget->widget_params->date_group) && $this->widget->widget_params->date_group == '%m %Y')
				$date_day = 1;

			if($date) {
				echo "dataTable.setValue(".$dates[$oneResult->calculated_date].", 0, new Date(".$oneResult->year.", ".(int)$oneResult->month.", ".(int)$date_day.", ".@(int)$oneResult->hour."));";
			} else {
				echo "dataTable.setValue(".$dates[$oneResult->calculated_date].", 0, '".hikashop_getDate(strtotime($date_day.'-'.($oneResult->month+1).'-'.$oneResult->year),'%d %B %Y')."');";
			}

		}
		if(!isset($types[$oneResult->type])){
			$types[$oneResult->type] = $a;
			echo "dataTable.addColumn('number','".$name."');"."\n";
			$a++;
		}
		echo "dataTable.setValue(".$dates[$oneResult->calculated_date].", ".$types[$oneResult->type].", ".$oneResult->total.");";
	}

	echo $dateFormat;
?>

	var vis = new google.visualization.<?php echo $chartType; ?>(document.getElementById('<?php echo 'chart_'.$id; ?>'));
	var options = {
		legend:'right',
		title: '<?php echo $name; ?>',
		legendTextStyle: {color:'#333333'},
		<?php echo $opt; ?>
	};
	vis.draw(dataTable, options);
}
google.load("visualization", "49", {packages:["corechart"]});
google.setOnLoadCallback(drawChart);
	</script>
<?php

if(isset($this->edit) && $this->edit){
	$size='width:80%; height:500px;';
}else{
	$size='width: 400px; height: 210px;';
}
?>
<div id="chart_<?php echo $id; ?>" class="chart hk_center" style="<?php echo $size; ?> margin:auto;"></div>
