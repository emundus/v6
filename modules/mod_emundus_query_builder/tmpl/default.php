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
	<button id="buttonOpen" class="btn" onclick="openCloseGraphManager()"><?php if(isset($_GET['gOpen'])) { echo JText::_('CLOSE_QUERY_BUILDER'); } else { echo JText::_('OPEN_QUERY_BUILDER'); } ?></button>
	<br /><br />
	<div class="queryBuilder" style="display:<?php if(isset($_GET['gOpen'])) { echo "block;"; } else { echo "none;"; } ?>" >
		<form action="" method="POST" onsubmit="return false;">
			<input type="button" id="createButton" class="btn" value="<?php echo JText::_('CREATE_MODULE'); ?>" onclick="buttonCreateModule()"/>
			<div class="createModule" id="createModule" style="display:none;" >
				<input type="text" id="titleModule" placeholder="<?php echo JText::_('TITLE_MODULE'); ?>*" />
				<label><?php echo JText::_('TYPE_MODULE'); ?>*</label>
				<select id="typeModule">
					<option value=""><?php echo JText::_('PLEASE_SELECT'); ?></option>
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
				<input type="text" id="axeXModule" placeholder="<?php echo JText::_('AXE_X_MODULE'); ?>*"  />
				<input type="text" id="axeYModule" placeholder="<?php echo JText::_('AXE_Y_MODULE'); ?>*" />
				<input type="text" id="progModule" placeholder="<?php echo JText::_('PROGRAM_LABEL'); ?>" />
				<input type="text" id="yearModule" placeholder="<?php echo JText::_('YEAR_LABEL'); ?>" />
				<input type="text" id="campaignModule" placeholder="<?php echo JText::_('CAMPAIGN_LABEL'); ?>" />
				<input type="button" name="validation" class="btn" id="validation" value="<?php echo JText::_('VALIDATION'); ?>" onclick="createModule()"/>
				<div id="errorCreateModule"></div>
			</div>
			<?php echo $showModule; ?>
		</form>
		<button onclick="getExport()" class="btn btnExport"><?php echo JText::_('EXPORT_MODULE'); ?></button>
	</div>
</center>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
	// Array which keeps the numbers of the chosen stats modules
	var tabNum = [];
	
	// Add or remove a number from the array
	function exportNum(num) {
		if(tabNum.indexOf(num) != -1)
			tabNum.splice(tabNum.indexOf(num), 1);
		else
			tabNum.push(num);
	}
	
	// Request the stats modules to export
	function getExport() {
		Swal.mixin({
			confirmButtonText: '<?php echo JText::_("VALIDATION"); ?>',
			cancelButtonText: '<?php echo JText::_("CANCEL"); ?>',
			showCancelButton: true
		}).queue([
			{
				title: "<?php echo JText::_('EXPORT_MODULE'); ?>",
				html: "<?php echo addslashes(str_replace(CHR(10),"",str_replace(CHR(13),"",$exportModule))) ?>"
			}
		]).then((result) => {
			if (result.value) {
				getPdf(tabNum);
			} else {
				tabNum = [];
			}
		})
	}
	
	// Create the images of the graphs and put them in the pdf which will download by itself
	async function getPdf(tab) {
		var s = document.createElement('a');
		var image = "";
		if(fusioncharts != undefined) {
			for(var cpt = 0 ; cpt < fusioncharts.length ; cpt++) {
				if(tab.indexOf(fusioncharts[cpt]["id"]) != -1) {
					svg = fusioncharts[cpt].getSVGString();
					blob = new Blob([svg], {type: 'image/svg+xml'});
					
					reader = new FileReader();

					reader.readAsText(blob);
					
					const result = await new Promise((resolve, reject) => {
						reader.onload = function(event) {
							resolve(reader.result)
						}
					})
					image = document.createElement('div');
					image.innerHTML = result;
					s.appendChild(image);
				}
			}
		}
		tabNum = [];
		
		jQuery.ajax({
			type : "POST",
			url : "index.php?option=com_ajax&module=emundus_query_builder&method=convertPdf&format=json",
			async: true,
			cache: false,
			data : {
				src: s.outerHTML
			},
			success : function(data) {
				console.log(data);
				data = JSON.parse(data.data);
				if (data.status != null) {
					elem = document.createElement('a');
					elem.href = "/tmp/Graph.pdf";
					elem.download = "Graph.pdf";
					evt = new MouseEvent("click", { bubbles: true,cancelable: true,view: window,});
					elem.dispatchEvent(evt);
					deleteFile();
				} else {
					console.log(data.msg);
				}
			}
		});
	}
	
	// Delete pdf from temporary files
	function deleteFile() {
		jQuery.ajax({
			type : "POST",
			url : "index.php?option=com_ajax&module=emundus_query_builder&method=deleteFile&format=json",
			async: true,
			cache: false,
			success : function(data) {
				console.log(data);
				data = JSON.parse(data.data);
				if (data.status != null) {
					console.log(data.msg);
				} else {
					console.log(data.msg);
				}
			}
		});
	}
	
	jQuery(function () {
		var premierItem = '';
		
		// Change the order of the stats modules
		jQuery('#sortable').sortable({
			cursor:"n-resize",
			containment: '.queryBuilder',
			handle:'.move',
			start: function(event, ui) {
				premierItem = document.getElementsByClassName('input')[0].className.substring(12);
			},
			update: function(event, ui) {
				var s = jQuery(this).sortable('toArray');
				
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
						msg = JSON.parse(data.data);
						if (msg.status) {
							refreshModuleGraphQueryBuilder();
						} else {
							console.log(msg.msg);
						}
					}
				});
			}
		});
	});
	
	// Display or not the statistics module manager
	function openCloseGraphManager() {
		if(document.getElementsByClassName('queryBuilder')[0].style.display === 'none') {
			document.getElementById('buttonOpen').innerHTML = "<?php echo JText::_('CLOSE_QUERY_BUILDER'); ?>";
			document.getElementsByClassName('queryBuilder')[0].style.display = 'block';
		} else {
			document.getElementById('buttonOpen').innerHTML = "<?php echo JText::_('OPEN_QUERY_BUILDER'); ?>";
			document.getElementsByClassName('queryBuilder')[0].style.display = 'none';
		}
	}
	
	// Allows you to refresh the stats modules dynamically
	function refreshModuleGraphQueryBuilder() {
		jQuery.ajax({
			type: 'POST',
			url: 'index.php?option=com_ajax&module=emundus_query_builder&method=reloadModule&format=json',
			dataType: 'html',
			success: function(response) {
				msg = JSON.parse(JSON.parse(response).data);
				if (msg.status) {
					if(fusioncharts != undefined) {
						for(var cpt = 0 ; cpt < fusioncharts.length ; cpt++)
							fusioncharts[cpt].dispose();
					}
					fusioncharts = [];
					var modulesString = msg.msg.split("////");
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
				} else {
					console.log(msg.msg);
				}
			}
		});
	}
	
	// Display or not the stat module creation form
	function buttonCreateModule() {
		var elt = document.getElementById("createModule");
		var button = document.getElementById("createButton");
		if(elt.style.display === "none") {
			elt.style.display = "block";
			button.value = "<?php echo JText::_('CANCEL'); ?>";
		} else {
			elt.style.display = "none";
			button.value = "<?php echo JText::_('CREATE_MODULE'); ?>";
		}
	}
	
	// Create user-made stat module
	function createModule() {
		if(document.getElementById("titleModule").value != "" && document.getElementById("typeModule").value != "" && document.getElementById("indicateurModule").value != "" && document.getElementById("axeXModule").value != "" && document.getElementById("axeYModule").value != "")
		{
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
					axeYModule: document.getElementById("axeYModule").value,
					progModule: document.getElementById("progModule").value,
					yearModule: document.getElementById("yearModule").value,
					campaignModule: document.getElementById("campaignModule").value,
					idMenu: <?php echo JFactory::getApplication()->getMenu()->getActive()->id; ?>
				},
				success : function(data) {
					if(data.success) {
						msg = JSON.parse(data.data);
						if (msg.status) {
							window.location.assign("<?php echo basename($_SERVER['REQUEST_URI']); ?>");
						} else {
							console.log(msg.msg);
						}
					} else {
						document.getElementById('errorCreateModule').innerHTML = "<?php echo JText::_('ERROR_CREATE_MODULE_2'); ?>";
					}
				}
			});
		} else {
			document.getElementById('errorCreateModule').innerHTML = "<?php echo JText::_('ERROR_CREATE_MODULE'); ?>";
		}
	}
	
	// Display or not the chosen stat module
	function changePublished(idModule) {
		jQuery.ajax({
			type : "POST",
			url : "index.php?option=com_ajax&module=emundus_query_builder&method=changePublishedModule&format=json",
			async: true,
			cache: false,
			data : {idChangePublishedModule: idModule},
			success : function(data) {
				msg = JSON.parse(data.data);
				if (msg.status) {
					window.location.assign("<?php echo basename($_SERVER['REQUEST_URI']); ?>");
				} else {
					console.log(msg.msg);
				}
			}
		});
	}
	
	// Modify the stat module chosen
	function modifyModule(idModule, titleModule, typeModule) {
		Swal.mixin({
			input: 'text',
			confirmButtonText: '<?php echo JText::_("NEXT"); ?> &rarr;',
			cancelButtonText: '<?php echo JText::_("CANCEL"); ?>',
			showCancelButton: true,
			progressSteps: ['1', '2']
		}).queue([
			{
				title: "<?php echo JText::_('TITLE_MODULE'); ?>",
				inputValue: ''+titleModule
			},
			{
				title: "<?php echo JText::_('TYPE_MODULE'); ?>",
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
						msg = JSON.parse(data.data);
						if (msg.status) {
							window.location.assign("<?php echo basename($_SERVER['REQUEST_URI']); ?>");
						} else {
							console.log(msg.msg);
						}
					}
				});
			}
		})
	}
	
	// Delete the stat module chosen
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
						msg = JSON.parse(data.data);
						if (msg.status) {
							window.location.assign("<?php echo basename($_SERVER['REQUEST_URI']); ?>");
						} else {
							console.log(msg.msg);
						}
					}
				});
			} else {
				Swal.fire("<?php echo JText::_('CANCEL'); ?>", "<?php echo JText::_('CANCEL_MESSAGE'); ?>", "error");
			}
		})
	}
	
	
</script>