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
// echo "<pre>".$jsonGraph."</pre>";
?>
<div class="<?php echo $module->id; ?>" style="display:none;"></div>
<center>
	<div class="container">
		<div id="chart-container-<?php echo $view; ?>"></div>
	</div>
	<br />
	<div class="btn"><i class="search icon"></i><a href="index.php?option=com_fabrik&task=list.view&listid=<?php echo $listId; ?>&Itemid=0<?php echo $urlFiltre; ?>">Consulter les données</a></div>
</center>

<script type="text/javascript" src="./plugins/fabrik_visualization/fusionchart/libs/fusioncharts-suite-xt/js/fusioncharts.js"></script>
<script type="text/javascript" src="./plugins/fabrik_visualization/fusionchart/libs/fusioncharts-suite-xt/js/themes/fusioncharts.theme.fusion.js"></script>
<script>
<?php if($jsonGraph != 'null') { ?>
	<?php if($typeGraph === "timeseries") { ?>
			data<?php echo $view; ?> = JSON.parse('<?php echo $jsonGraph; ?>');
			schema<?php echo $view; ?> = JSON.parse('[{"name": "<?php echo $yAxeName; ?>","type": "number"}, {"name": "<?php echo $xAxeName; ?>", "type": "date", "format": "%Y-%m-%d %I:%M:%S"}]');


			dataStore = new FusionCharts.DataStore();
			dataSource<?php echo $view; ?> = {
				chart: {
					yaxisname: "<?php echo $yAxeName; ?>",
					theme: "fusion"
				},
				caption: {
					text: "<?php echo $titleGraph; ?>"
				},
				yAxis: [
					{
						plot: {
							value: "<?php echo $yAxeName; ?>",
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
			caption: "<?php echo $titleGraph; ?>",
			<?php if(substr_count($typeGraph, "dy") != 0) {
				echo "pYAxisName:\"".$yAxeName."\",";
				echo "sYAxisName:\"".$yAxeName1."\",";
			} else { ?>
			yaxisname: "<?php echo $yAxeName; ?>",
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
	chartConfig<?php echo $view; ?> = {
		id: "<?php echo $view; ?>",
		type: "<?php echo $typeGraph; ?>",
		renderAt: "chart-container-<?php echo $view; ?>",
		width: "100%",
		height: "500"
		<?php if($jsonGraph != 'null') { ?>
		,
		dataSource: dataSource<?php echo $view; ?>
		<?php } ?>
	};
	
	if(FusionCharts("<?php echo $view; ?>") != undefined)
		FusionCharts("<?php echo $view; ?>").dispose();
	if(fusioncharts === undefined) {
		var fusioncharts = new Array();
	}
	fusioncharts.push(new FusionCharts(chartConfig<?php echo $view; ?>));
    fusioncharts[fusioncharts.length-1].render();

</script>