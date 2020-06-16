<?php
defined('_JEXEC') or die;
header('Content-Type: text/html; charset=utf-8');
$document = JFactory::getDocument();
//Chart.js is the libary used for this module's graphs
$document->addScript('media'.DS.'com_emundus'.DS.'lib'.DS.'Chart.min.js');
//moment.js is a Date libary, using to retrieve missing dates
$document->addScript('media'.DS.'com_emundus'.DS.'lib'.DS.'moment.min.js');
$document->addStyleSheet('media'.DS.'com_emundus'.DS.'lib'.DS.'bootstrap-336'.DS.'css'.DS.'bootstrap.min.css');
$document->addStyleSheet('media'.DS.'com_emundus'.DS.'lib'.DS.'Semantic-UI-CSS-master'.DS.'semantic.min.css');
?>

<!-- Div to locate the graphs for the filter and the query builder -->
<div class="<?php echo $module->id; ?> moduleGraphe" style="display:none;"></div>

<center>
	<div class="container">
		<!-- Div containing the graph -->
		<div id="chart-container-<?php echo $view; ?>"></div>
	</div>
	<br />
	<!-- Button to consult the data of the graph -->
	<div class="btn"><i class="search icon"></i><a href="\index.php?option=com_fabrik&task=list.view&listid=<?php echo $listId; ?>&Itemid=0<?php echo $urlFiltre; ?>" target="_blank"><?php echo JText::_('VIEW_DATA')?></a></div>
</center>

<script type="text/javascript" src="./plugins/fabrik_visualization/fusionchart/libs/fusioncharts-suite-xt/js/fusioncharts.js"></script>
<script type="text/javascript" src="./plugins/fabrik_visualization/fusionchart/libs/fusioncharts-suite-xt/js/themes/fusioncharts.theme.fusion.js"></script>

<script>
<?php if($jsonGraph != 'null') { ?>
	<?php if($typeGraph === "timeseries") { ?>
		data<?php echo $view; ?> = JSON.parse('<?php echo $jsonGraph; ?>');
		schema<?php echo $view; ?> = JSON.parse('[{"name": "<?php echo JText::_($yAxeName); ?>","type": "number"}, {"name": "<?php echo JText::_($xAxeName); ?>", "type": "date", "format": "%Y-%m-%d %I:%M:%S"}]');


		dataStore = new FusionCharts.DataStore();
		dataSource<?php echo $view; ?> = {
			chart: {
				yaxisname: "<?php echo JText::_($yAxeName); ?>",
				theme: "fusion"
			},
			caption: {
				text: "<?php echo JText::_($titleGraph); ?>"
			},
			yAxis: [
				{
					plot: {
						value: "<?php echo JText::_($yAxeName); ?>",
						type: "column",
						aggregation: "Sum"
					}
				}
			]
		};
		dataSource<?php echo $view; ?>.data = dataStore.createDataTable(data<?php echo $view; ?>, schema<?php echo $view; ?>);
	<?php } else { ?>
		dataSource<?php echo $view; ?> = {
			chart: {
				caption: "<?php echo JText::_($titleGraph); ?>",
				<?php if(substr_count($typeGraph, "dy") != 0) {
					echo "pYAxisName:\"".JText::_($yAxeName)."\",";
					echo "sYAxisName:\"".JText::_($yAxeName1)."\",";
				} else { ?>
					yaxisname: "<?php echo JText::_($yAxeName); ?>",
				<?php } ?>
				theme: "fusion"
			},
			<?php if(strrpos($typeGraph, "scroll") === 0) { 
				echo substr($jsonGraph, 1, -1);
			} elseif(strrpos($typeGraph, "ms") === 0 || strrpos($typeGraph, "stacked") === 0 || strrpos($typeGraph, "marimekko") === 0 || strrpos($typeGraph, "zoom") === 0 || strrpos($typeGraph, "over") === 0) { 
				echo substr($jsonGraph, 1, -1);
			} else { ?>
			data: <?php echo $jsonGraph; ?>
			<?php } ?>
		};
	<?php } ?>
<?php } ?>
	
	// Configuration of the graph
	chartConfig<?php echo $view; ?> = {
		id: "<?php echo $view; ?>",
		type: "<?php echo JText::_($typeGraph); ?>",
		renderAt: "chart-container-<?php echo $view; ?>",
		width: "100%",
		height: "500"
		<?php if($jsonGraph != 'null') { ?>
		,
		dataSource: dataSource<?php echo $view; ?>
		<?php } ?>
	};
	
	// Array which keeps the graphs and if there is already one of the same view, we remove its display
	if(FusionCharts("<?php echo $view; ?>") != undefined)
		FusionCharts("<?php echo $view; ?>").dispose();
	if(fusioncharts === undefined) {
		var fusioncharts = new Array();
	}
	fusioncharts.push(new FusionCharts(chartConfig<?php echo $view; ?>));
	
	// We display the graph created
    fusioncharts[fusioncharts.length-1].render();

</script>