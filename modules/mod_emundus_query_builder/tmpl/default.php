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

<center>
	<div class="queryBuilder">
		<form action="" method="POST" onsubmit="return false;">
			<input type="button" id="createButton" class="btn" value="CREATE_MODULE" onclick="buttonCreateModule()"/>
			<div class="createModule" id="createModule" style="display:none;">
				<input type="text" id="titleModule" placeholder="Titre du graphe" />
				<label>Type</label>
				<select id="typeModule">
					<option value="timeseries"><?php echo JText::_("LINE_TIME_LABEL") ?></option>
					<option value="column2d"><?php echo JText::_("COLUMN_LABEL") ?></option>
					<option value="column3d"><?php echo JText::_("COLUMN_3D_LABEL") ?></option>
					<option value="scrollcolumn2d"><?php echo JText::_("COLUMN_SCROLL_LABEL") ?></option>
					<option value="line"><?php echo JText::_("LINE_LABEL") ?></option>
					<option value="scrollline2d"><?php echo JText::_("LINE_SCROLL_LABEL") ?></option>
					<option value="area2d"><?php echo JText::_("AREA_LABEL") ?></option>
					<option value="scrollarea2d"><?php echo JText::_("AREA_SCROLL_LABEL") ?></option>
					<option value="bar2d"><?php echo JText::_("BAR_LABEL") ?></option>
					<option value="bar3d"><?php echo JText::_("BAR_3D_LABEL") ?></option>
					<option value="scrollbar2d"><?php echo JText::_("BAR_SCROLL_LABEL") ?></option>
					<option value="pie2d"><?php echo JText::_("PIE_LABEL") ?></option>
					<option value="pie3d"><?php echo JText::_("PIE_3D_LABEL") ?></option>
					<option value="doughnut2d"><?php echo JText::_("DOUGHNUT_LABEL") ?></option>
					<option value="doughnut3d"><?php echo JText::_("DOUGHNUT_3D_LABEL") ?></option>
					<option value="pareto2d"><?php echo JText::_("PARETO_LABEL") ?></option>
					<option value="pareto3d"><?php echo JText::_("PARETO_3D_LABEL") ?></option>
				</select>
				<?php echo $selectIndicateur; ?>
				<input type="text" id="axeXModule" placeholder="Nom de l'axe X" />
				<input type="text" id="axeYModule" placeholder="Nom de l'axe Y" />
				<input type="button" name="validation" class="btn" value="VALIDATION" onclick="createModule()"/>
			</div>
			<?php echo $showModule; ?>
		</form>
	</div>
</center>

<!--<div id="debug"></div>-->

<script src="https://cdn.jsdelivr.net/npm/sweetalert"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
<script>
	jQuery(function () {
		var premierItem = '';
		jQuery('#sortable').sortable({
			cursor:"n-resize",
			containment: '.queryBuilder',
			handle:'.move',
			start: function(event, ui) {
				premierItem = document.getElementsByClassName('input')[0].className.substring(12);
			},
			update: function(event, ui) {
				var s = jQuery(this).sortable('toArray');
				console.log(premierItem);
				
				jQuery.ajax({
					type : "POST",
					url : "index.php?option=com_ajax&module=emundus_query_builder&method=changeOrderModule&format=json",
					async: true,
					cache: false,
					data : {
						id: s,
						order1: premierItem
					},
					success : function(data) {
						refreshModuleGraphQueryBuilder();
					}
				});
			}
		});
	});
	
	function refreshModuleGraphQueryBuilder() {
		jQuery.ajax({
			type: 'POST',
			url: 'index.php?option=com_ajax&module=emundus_stat_filter&method=reloadModule&format=json',
			dataType: 'html',
			success: function(response) {
				if(fusioncharts != undefined) {
					for(var cpt = 0 ; cpt < fusioncharts.length ; cpt++)
						fusioncharts[cpt].dispose();
				}
				var modulesString = JSON.parse(response).data.split("////");
				var cpt0 = 0;
				for(var cpt = 1 ; cpt < modulesString.length ; cpt++) {
					for(var i = 0 ; cpt0 < document.getElementsByClassName('moduletable').length &&
					document.getElementsByClassName('moduletable')[cpt0].getElementsByClassName("moduleGraphe").length <= 0 ; i++) {
						cpt0++;
					}
					cpt++;
					document.getElementsByClassName('moduletable')[cpt0].innerHTML = modulesString[cpt];
					var scripts = document.getElementsByClassName('moduletable')[cpt0].getElementsByTagName('script');
					for(var i=0; i < scripts.length;i++)
					{
						if (window.execScript)
						{
							window.execScript(scripts[i].text.replace('<!--',''));
						}
						else
						{
							window.eval(scripts[i].text);
						}
					}
					
					cpt0++;
				}
				
				if(fusioncharts != undefined) {
					for(var cpt = 0 ; cpt < fusioncharts.length ; cpt++)
						fusioncharts[cpt].render();
				}
			}
		});
	}
	
	function buttonCreateModule() {
		var elt = document.getElementById("createModule");
		var button = document.getElementById("createButton");
		if(elt.style.display === "none") {
			elt.style.display = "block";
			button.value = "CANCEL";
		} else {
			elt.style.display = "none";
			button.value = "CREATE_MODULE";
		}
	}
	
	function createModule() {
		jQuery.ajax({
			type : "POST",
			url : "index.php?option=com_ajax&module=emundus_query_builder&method=createModule&format=json",
			async: true,
			cache: false,
			data : {
				titleModule: document.getElementById("titleModule").value,
				typeModule: document.getElementById("typeModule").value,
				indicateurModule: document.getElementById("indicateurModule").value,
				axeXModule: document.getElementById("axeXModule").value,
				axeYModule: document.getElementById("axeYModule").value
			},
			success : function(data) {
				window.location.assign("<?php echo basename($_SERVER['REQUEST_URI']); ?>");
			}
		});
	}
	
	function changePublished(idModule) {
		jQuery.ajax({
			type : "POST",
			url : "index.php?option=com_ajax&module=emundus_query_builder&method=changePublishedModule&format=json",
			async: true,
			cache: false,
			data : {idChangePublishedModule: idModule},
			success : function(data) {
				window.location.assign("<?php echo basename($_SERVER['REQUEST_URI']); ?>");
			}
		});
	}
	
	function modifyModule(idModule, titleModule, typeModule) {
		Swal.mixin({
			input: 'text',
			confirmButtonText: 'Next &rarr;',
			showCancelButton: true,
			progressSteps: ['1', '2']
		}).queue([
			{
				title: 'Title',
				inputValue: ''+titleModule
			},
			{
				title: 'Type',
				input: 'select',
				inputOptions: {
					timeseries: '<?php echo JText::_("LINE_TIME_LABEL") ?>',
					column2d: '<?php echo JText::_("COLUMN_LABEL") ?>',
					column3d: '<?php echo JText::_("COLUMN_3D_LABEL") ?>',
					scrollcolumn2d: '<?php echo JText::_("COLUMN_SCROLL_LABEL") ?>',
					line: '<?php echo JText::_("LINE_LABEL") ?>',
					scrollline2d: '<?php echo JText::_("LINE_SCROLL_LABEL") ?>',
					area2d: '<?php echo JText::_("AREA_LABEL") ?>',
					scrollarea2d: '<?php echo JText::_("AREA_SCROLL_LABEL") ?>',
					bar2d: '<?php echo JText::_("BAR_LABEL") ?>',
					bar3d: '<?php echo JText::_("BAR_3D_LABEL") ?>',
					scrollbar2d: '<?php echo JText::_("BAR_SCROLL_LABEL") ?>',
					pie2d: '<?php echo JText::_("PIE_LABEL") ?>',
					pie3d: '<?php echo JText::_("PIE_3D_LABEL") ?>',
					doughnut2d: '<?php echo JText::_("DOUGHNUT_LABEL") ?>',
					doughnut3d: '<?php echo JText::_("DOUGHNUT_3D_LABEL") ?>',
					pareto2d: '<?php echo JText::_("PARETO_LABEL") ?>',
					pareto3d: '<?php echo JText::_("PARETO_3D_LABEL") ?>'
				},
				inputValue: typeModule
			}
		]).then((result) => {
			if (result.value) {
				const answers = result.value;
				jQuery.ajax({
					type : "POST",
					url : "index.php?option=com_ajax&module=emundus_query_builder&method=changeModule&format=json",
					async: true,
					cache: false,
					data : {idModifyModule: idModule, titleModule: answers[0], typeModule: answers[1]},
					success : function(data) {
						window.location.assign("<?php echo basename($_SERVER['REQUEST_URI']); ?>");
					}
				});
			}
		})
	}
	
	function deleteModule(idModule) {
		
		Swal.fire({
			title: "<?php echo JText::_('ASK'); ?>",
			text: "<?php echo JText::_('WARNING'); ?>",
			icon: "warning",
			showCancelButton: true,
			confirmButtonText: '<?php echo JText::_("ANSWER_2"); ?>',
			cancelButtonText: '<?php echo JText::_("ANSWER_1"); ?>',
			dangerMode: true,
		}).then(function(result) {
			if (result.value) {
				jQuery.ajax({
					type : "POST",
					url : "index.php?option=com_ajax&module=emundus_query_builder&method=deleteModule&format=json",
					async: true,
					cache: false,
					data : {idDeleteModule: idModule},
					success : function(data) {
						window.location.assign("<?php echo basename($_SERVER['REQUEST_URI']); ?>");
					}
				});
			} else {
				Swal.fire("<?php echo JText::_('CANCEL'); ?>", "<?php echo JText::_('CANCEL_MESSAGE'); ?>", "error");
			}
		})
	}
</script>