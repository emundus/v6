<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$data = array();
$i=0;
if(isset($this->edit)){
	$showLegend='true';
}else{
	$showLegend='false';
}
if(empty($this->widget->elements)){
	$data[] = 'data.setValue(0, 0, null);
			data.setValue(0, 1, 0);
			data.setValue(0, 2, null);';
}else{
	foreach($this->widget->elements as $element){
		$data[] = 'data.setValue('.$i.', 0, \''.str_replace("'","\'",$element->zone_code_2).'\');
					data.setValue('.$i.', 1, '.(int)$element->total.');';
		$i++;
	}
}

if(isset($this->edit) && $this->edit){
	$size="";
	$height="350";
}else{
	$size="options['width'] = '300px';
				options['height'] = '210px'";
				$height="210";
}

$js="
google.load('visualization', '49', {'packages':['geochart']});
			google.setOnLoadCallback(drawChart_".$this->widget->widget_id.");
			function drawChart_".$this->widget->widget_id."() {
				var data = new google.visualization.DataTable();
				data.addColumn('string', 'Code');
				data.addColumn('number', '".JText::_(strtoupper($this->widget->widget_params->content))."');
				data.addRows(".count($data).");
				".implode("\n",$data)."
		var options = {};
				".$size."
				options['showLegend'] = ".$showLegend.";
				options['region']='".$this->widget->widget_params->region."';
				var chart = new google.visualization.GeoChart(document.getElementById('graph_".$this->widget->widget_id."'));
				chart.draw(data, options);
			}";
$doc = JFactory::getDocument();
$doc->addScriptDeclaration($js);
?>
<div id="graph_<?php echo $this->widget->widget_id; ?>" style="height: <?php echo $height; ?>px;" class="hk_center"></div>
