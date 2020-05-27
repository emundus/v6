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
			<div class="createModule" id="createModule">
				<input type="text" id="titleModule" placeholder="Titre du graphe" />
				<label>Type</label>
				<select id="typeModule">
					<option value="timeseries"><?php echo JText::_("LINE_TIME_LABEL") ?></option>
					<option value="column2d"><?php echo JText::_("COLUMN_LABEL") ?></option>
					<option value="column3d"><?php echo JText::_("COLUMN_3D_LABEL") ?></option>
					<option value="scrollcolumn2d"><?php echo JText::_("COLUMN_SCROLL_LABEL") ?></option>
					<option value="line"><?php echo JText::_("LINE_LABEL") ?></option>
					<option value="scrollline2d"><?php echo JText::_("LINE_SCROLL_LABEL") ?></option>
					<option value="area2d"><?php echo JText::_("LINE_TIME_LABEL") ?></option>
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
<script>
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
			url : "<?php echo basename($_SERVER['REQUEST_URI']); ?>",
			data : {
				createModule: true,
				titleModule: document.getElementById("titleModule").value,
				typeModule: document.getElementById("typeModule").value,
				indicateurModule: document.getElementById("indicateurModule").value,
				axeXModule: document.getElementById("axeXModule").value,
				axeYModule: document.getElementById("axeYModule").value
			},
			success : function(data) {
				console.log(data);
				// window.location.assign("<?php echo basename($_SERVER['REQUEST_URI']); ?>");
			}
		});
	}
	
	function changePublished(idModule) {
		jQuery.ajax({
			type : "POST",
			url : "<?php echo basename($_SERVER['REQUEST_URI']); ?>",
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
					area2d: '<?php echo JText::_("LINE_TIME_LABEL") ?>',
					scrollarea2d: '<?php echo JText::_("AREA_SCROLL_LABEL") ?>',
					bar2d: '<?php echo JText::_("BAR_LABEL") ?>',
					bar3d: '<?php echo JText::_("BAR_3D_LABEL") ?>',
					scrollbar2d: '<?php echo JText::_("BAR_SCROLL_LABEL") ?>',
					pie2d: '<?php echo JText::_("PIE_LABEL") ?>',
					pie3d: '<?php echo JText::_("PIE_3D_LABEL") ?>',
					doughnut2d: '<?php echo JText::_("DOUGHNUT_LABEL") ?>',
					doughnut3d: '<?php echo JText::_("DOUGHNUT_3D_LABEL") ?>',
					pareto2d: '<?php echo JText::_("PARETO_LABEL") ?>',
					pareto3d: '<?php echo JText::_("PARETO_3D_LABEL") ?>',
					mscolumn2d: '<?php echo JText::_("MS_COLUMN_LABEL") ?>',
					stackedcolumn2d: '<?php echo JText::_("COLUMN_STACKED_LABEL") ?>',
					scrollstackedcolumn2d: '<?php echo JText::_("SCROLL_COLUMN_STACKED_LABEL") ?>',
					msstackedcolumn2d: '<?php echo JText::_("MS_COLUMN_STACKED_LABEL") ?>',
					scrollmsstackedcolumn2d: '<?php echo JText::_("SCROLL_MS_COLUMN_STACKED_LABEL") ?>',
					mscolumn3d: '<?php echo JText::_("MS_COLUMN_3D_LABEL") ?>',
					stackedcolumn3d: '<?php echo JText::_("COLUMN_STACKED_3D_LABEL") ?>',
					msline: '<?php echo JText::_("MS_LINE_LABEL") ?>',
					msbar2d: '<?php echo JText::_("MS_BAR_LABEL") ?>',
					stackedbar2d: '<?php echo JText::_("BAR_STACKED_LABEL") ?>',
					scrollstackedbar2d: '<?php echo JText::_("SCROLL_BAR_STACKED_LABEL") ?>',
					msbar3d: '<?php echo JText::_("MS_BAR_3D_LABEL") ?>',
					stackedbar3d: '<?php echo JText::_("BAR_STACKED_3D_LABEL") ?>',
					overlappedcolumn2d: '<?php echo JText::_("OVERLAPPED_COLUMN_LABEL") ?>',
					overlappedbar2d: '<?php echo JText::_("OVERLAPPED_BAR_LABEL") ?>',
					msarea: '<?php echo JText::_("MS_AREA_LABEL") ?>',
					stackedarea2d: '<?php echo JText::_("AREA_STACKED_LABEL") ?>',
					marimekko: '<?php echo JText::_("MARIMEKKO_LABEL") ?>',
					zoomline: '<?php echo JText::_("ZOOMLINE_LABEL") ?>',
					zoomlinedy: '<?php echo JText::_("ZOOMLINE_YD_LABEL") ?>',
					mscombi2d: '<?php echo JText::_("MS_COMBI_LABEL") ?>',
					mscombidy2d: '<?php echo JText::_("MS_COMBI_DY_LABEL") ?>',
					mscombi3d: '<?php echo JText::_("MS_COMBI_3D_LABEL") ?>',
					mscombidy3d: '<?php echo JText::_("MS_COMBI_DY_3D_LABEL") ?>',
					mscolumnline3d: '<?php echo JText::_("MS_COLUMN_3D_LINE_LABEL") ?>',
					mscolumn3dlinedy: '<?php echo JText::_("MS_COLUMN_3D_LINE_DY_LABEL") ?>',
					stackedcolumn2dline: '<?php echo JText::_("COLUMN_STACKED_LINE_LABEL") ?>',
					stackedcolumn3dline: '<?php echo JText::_("COLUMN_STACKED_3D_LINE_LABEL") ?>',
					stackedcolumn2dlinedy: '<?php echo JText::_("COLUMN_STACKED_LINE_DY_LABEL") ?>',
					stackedcolumn3dlinedy: '<?php echo JText::_("COLUMN_STACKED_3D_LINE_DY_LABEL") ?>',
					stackedarea2dlinedy: '<?php echo JText::_("AREA_STACKED_LINE_DY_LABEL") ?>',
					msstackedcolumn2dlinedy: '<?php echo JText::_("MS_COLUMN_STACKED_LINE_DY_LABEL") ?>',
					scrollmsstackedcolumn2dlinedy: '<?php echo JText::_("SCROLL_MS_COLUMN_STACKED_LINE_DY_LABEL") ?>',
					scrollcombi2d: '<?php echo JText::_("COMBI_SCROLL_LABEL") ?>',
					scrollcombidy2d: '<?php echo JText::_("COMBI_DUAL_SCROLL_LABEL") ?>'
				}
			}
		]).then((result) => {
			if (result.value) {
				const answers = result.value;
				jQuery.ajax({
					type : "POST",
					url : "<?php echo basename($_SERVER['REQUEST_URI']); ?>",
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
					url : "<?php echo basename($_SERVER['REQUEST_URI']); ?>",
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