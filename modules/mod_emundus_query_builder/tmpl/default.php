<?php
defined('_JEXEC') or die;
header('Content-Type: text/html; charset=utf-8');
?>


<div>
	<button id="buttonOpen" class="btnQueryBuilder" onclick="openCloseGraphManager()"><?php if(isset($_GET['gOpen'])) { echo JText::_('CLOSE_QUERY_BUILDER'); } else { echo JText::_('OPEN_QUERY_BUILDER'); } ?></button>
	<br /><br />
	<div class="queryBuilder" style="display:<?php if(isset($_GET['gOpen'])) { echo "block;"; } else { echo "none;"; } ?>" >
		<form action="" method="POST" onsubmit="return false;">
			<?php echo $showModule; ?>
			<div class="createModule" id="createModule" style="display:none;" >
				<input type="text" id="titleModule" placeholder="<?php echo JText::_('MOD_EMUNDUS_QUERY_BUILDER_TITLE_MODULE'); ?>*" />
				<div class="flexS">
					<label><?php echo JText::_('MOD_EMUNDUS_QUERY_BUILDER_TYPE_MODULE'); ?>*</label>
					<select id="typeModule">
						<option value=""><?php echo JText::_('MOD_EMUNDUS_QUERY_BUILDER_PLEASE_SELECT'); ?></option>
						<option value="timeseries"><?php echo JText::_("MOD_EMUNDUS_QUERY_BUILDER_LINE_TIME_LABEL") ?></option>
						<option value="column2d"><?php echo JText::_("MOD_EMUNDUS_QUERY_BUILDER_COLUMN_LABEL") ?></option>
						<option value="column3d"><?php echo JText::_("MOD_EMUNDUS_QUERY_BUILDER_COLUMN_3D_LABEL") ?></option>
						<option value="scrollcolumn2d"><?php echo JText::_("MOD_EMUNDUS_QUERY_BUILDER_COLUMN_SCROLL_LABEL") ?></option>
						<option value="line"><?php echo JText::_("MOD_EMUNDUS_QUERY_BUILDER_LINE_LABEL") ?></option>
						<option value="scrollline2d"><?php echo JText::_("MOD_EMUNDUS_QUERY_BUILDER_LINE_SCROLL_LABEL") ?></option>
						<option value="area2d"><?php echo JText::_("MOD_EMUNDUS_QUERY_BUILDER_AREA_LABEL") ?></option>
						<option value="scrollarea2d"><?php echo JText::_("MOD_EMUNDUS_QUERY_BUILDER_AREA_SCROLL_LABEL") ?></option>
						<option value="bar2d"><?php echo JText::_("MOD_EMUNDUS_QUERY_BUILDER_BAR_LABEL") ?></option>
						<option value="bar3d"><?php echo JText::_("MOD_EMUNDUS_QUERY_BUILDER_BAR_3D_LABEL") ?></option>
						<option value="scrollbar2d"><?php echo JText::_("MOD_EMUNDUS_QUERY_BUILDER_BAR_SCROLL_LABEL") ?></option>
						<option value="pie2d"><?php echo JText::_("MOD_EMUNDUS_QUERY_BUILDER_PIE_LABEL") ?></option>
						<option value="pie3d"><?php echo JText::_("MOD_EMUNDUS_QUERY_BUILDER_PIE_3D_LABEL") ?></option>
						<option value="doughnut2d"><?php echo JText::_("MOD_EMUNDUS_QUERY_BUILDER_DOUGHNUT_LABEL") ?></option>
						<option value="doughnut3d"><?php echo JText::_("MOD_EMUNDUS_QUERY_BUILDER_DOUGHNUT_3D_LABEL") ?></option>
						<option value="pareto2d"><?php echo JText::_("MOD_EMUNDUS_QUERY_BUILDER_PARETO_LABEL") ?></option>
						<option value="pareto3d"><?php echo JText::_("MOD_EMUNDUS_QUERY_BUILDER_PARETO_3D_LABEL") ?></option>
					</select>
					<?php echo $selectIndicateur; ?>
				</div>
				<div class="flexS">
					<input type="text" id="axeXModule" placeholder="<?php echo JText::_('MOD_EMUNDUS_QUERY_BUILDER_AXE_X_MODULE'); ?>*"  />
					<input type="text" id="axeYModule" placeholder="<?php echo JText::_('MOD_EMUNDUS_QUERY_BUILDER_AXE_Y_MODULE'); ?>*" />
				</div>
				<input type="text" id="progModule" placeholder="<?php echo JText::_('MOD_EMUNDUS_QUERY_BUILDER_PROGRAM_LABEL'); ?>" />
				<div class="flexS">
					<input type="text" id="yearModule" placeholder="<?php echo JText::_('MOD_EMUNDUS_QUERY_BUILDER_YEAR_LABEL'); ?>" />
					<input type="text" id="campaignModule" placeholder="<?php echo JText::_('MOD_EMUNDUS_QUERY_BUILDER_CAMPAIGN_LABEL'); ?>" />
				</div>
				<div id="errorCreateModule"></div>
			</div>
            <input type="button" name="validation" class="btnQueryBuilder" id="validation" style="float:right;display:none;" value="<?php echo JText::_('MOD_EMUNDUS_QUERY_BUILDER_VALIDATION'); ?>" onclick="createModule()"/>
			<input type="button" id="createButton" class="btnQueryBuilder" style="float:right;" value="<?php echo JText::_('MOD_EMUNDUS_QUERY_BUILDER_CREATE_MODULE'); ?>" onclick="buttonCreateModule()"/>
            <?php if (!empty($gotenberg_activation)) :?>
			    <button onclick="getExport()" class="btnExport" style="float:left;"> <?php echo JText::_('MOD_EMUNDUS_QUERY_BUILDER_EXPORT_MODULE'); ?></button>
            <?php endif; ?>
		</form>
	</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
jQuery(document).ready(function(){
	var element1 = document.getElementsByClassName('filter')[0].parentNode.parentNode.parentNode;
	var element2 = document.getElementsByClassName('queryBuilder')[0].parentNode.parentNode.parentNode;
	var parent = document.getElementsByClassName('g-content')[0];
	var wrapper1 = document.createElement('div');
	element1.replaceWith(wrapper1);
	wrapper1.appendChild(element1);
	wrapper1.appendChild(element2);
	wrapper1.classList.add("etiquette");

	var wrapper2 = document.createElement('div');
	wrapper1.parentNode.insertBefore(wrapper2, wrapper1);
	wrapper2.classList.add("informationStatistique");
	wrapper2.innerHTML = "<a class='closeButtonWelcomeStat' onclick='deleteWelcomeStat()'></a><?php echo JText::_('MOD_EMUNDUS_QUERY_BUILDER_TEXT_WELCOME_STAT'); ?>";

	jQuery('.g-content').has('.etiquette').attr('style', 'margin:auto!important');
	jQuery('.g-content').has('.etiquette').attr('style', 'display:grid;grid-column-gap:15px;grid-row-gap:15px;grid-template-columns:repeat(3, 1fr);margin-bottom:15px!important;');
	jQuery('.platform-content').attr('style', 'width:100%!important');
	jQuery('.platform-content').has('.container-stat').attr('style', 'margin-bottom:50px;background-color:white;padding-bottom:1%;box-shadow: 0 1px 2px 0 hsla(0,0%,41.2%,.19);');
	jQuery('.etiquette').attr('style', 'grid-column-end:span 3;');
	jQuery('.informationStatistique').attr('style', 'grid-column-end:span 3;margin-top: 32px');
	jQuery('#g-container-main').attr('style', 'padding-left:5%!important;padding-right:5%!important');

	taillerEtiquette();
});

	function deleteWelcomeStat() {
		document.getElementsByClassName('informationStatistique')[0].style.display = "none";
	}

	function taillerEtiquette() {
		var u = 0;
		var tab = jQuery('.platform-content').has(".container-stat");
		var elt = null;
		if (screen.width < 951) {
			for(var i = 0; i < tab.length;i++)
				tab[i].style.gridColumnEnd = "span 3";
		} else {
			for(var i = 0; i < tab.length;i++) {
				elt = tab[i].children[0].children[1].children[0].className.split(' ');
				if(elt[0] === "container-stat") {
					if(u === 3)
						u = 0;

					if(!elt[1].includes("doughnut") && !elt[1].includes("pie")) {
						tab[i].style.gridColumnEnd = "span 2";
						u = u + 2;

						if(i % 2)
							o = -1;
						else
							o = 1;

						if(	(tab[i+o] != null &&
							!tab[i+o].children[0].children[1].children[0].className.includes("container-stat doughnut") &&
							!tab[i+o].children[0].children[1].children[0].className.includes("container-stat pie"))
							|| (tab[i+o] === undefined)) {
							tab[i].style.gridColumnEnd = "span 3";
							u = 0;
						}
					}else{
						tab[i].style.gridColumnEnd = "span 1";
						u = u + 1;
						if(u === 2) {
							tab[i-(Math.floor(Math.random() * Math.floor(2)))].style.gridColumnEnd = "span 2";
							u = 0;
						}
					}
				}
			}
		}
	}

	// Array which keeps the numbers of the chosen stats modules
	var tabNum = [];

	// Add or remove a number from the array
	function exportNum(num) {
		if (tabNum.indexOf(num) != -1) {
		    tabNum.splice(tabNum.indexOf(num), 1);
        } else {
		    tabNum.push(num);
        }
	}

	// Request the stats modules to export
	function getExport() {
		Swal.mixin({
			confirmButtonText: '<?php echo JText::_("MOD_EMUNDUS_QUERY_BUILDER_EXPORT_MODULE_2"); ?>',
			cancelButtonText: '<?php echo JText::_("CANCEL"); ?>',
			showCancelButton: true
		}).queue([
			{
				title: "<?php echo JText::_('MOD_EMUNDUS_QUERY_BUILDER_CHOOSE_EXPORT_MODULE'); ?>",
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
		if (typeof fusioncharts !== 'undefined') {

		    for(var cpt = 0; cpt < fusioncharts.length; cpt++) {

				if (tab.indexOf(fusioncharts[cpt]["id"]) != -1) {
					svg = fusioncharts[cpt].getSVGString();
					blob = new Blob([svg], {type: 'image/svg+xml'});

					reader = new FileReader();

					reader.readAsText(blob);

					const result = await new Promise((resolve) => {
						reader.onload = function() {
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
				data = JSON.parse(data.data);
				if (data.status != null) {
					elem = document.createElement('a');
					elem.href = "/tmp/Graph.pdf";
					elem.download = "Graph.pdf";
					evt = new MouseEvent("click", { bubbles: true,cancelable: true,view: window});
					elem.dispatchEvent(evt);
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
			start: function() {
				premierItem = document.getElementsByClassName('input')[0].className.substring(12);
			},
			update: function() {
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
			document.getElementById("createButton").value = "<?php echo JText::_('MOD_EMUNDUS_QUERY_BUILDER_CREATE_MODULE'); ?>";
			document.getElementById("createButton").classList.replace("btnExport", "btnQueryBuilder");
			document.getElementById("createButton").style.float = "right";
			document.getElementById('buttonOpen').innerHTML = "<?php echo JText::_('OPEN_QUERY_BUILDER'); ?>";
			document.getElementsByClassName('queryBuilder')[0].style.display = 'none';
			document.getElementById('createModule').style.display = "none";
			document.getElementById('validation').style.display = "none";
			document.getElementById('sortable').style.display = "block";
			document.getElementsByClassName('filter')[0].parentNode.parentNode.style.display = "block";
			document.getElementsByClassName('btnExport')[0].style.display = "block";
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

					if (typeof fusioncharts !== "undefined") {
						for (var cpt = 0; cpt < fusioncharts.length; cpt++) {
						    fusioncharts[cpt].dispose();
                        }
					}
					fusioncharts = [];
					var modulesString = msg.msg.split("////");
					var cpt0 = 0;
					for(var cpt = 1 ; cpt < modulesString.length ; cpt++) {
						for(var i = 0 ; cpt0 < document.getElementsByClassName('moduletable').length && document.getElementsByClassName('moduletable')[cpt0].getElementsByClassName("moduleGraphe").length <= 0 ; i++) {
							cpt0++;
						}
						cpt++;
						document.getElementsByClassName('moduletable')[cpt0].innerHTML = modulesString[cpt];
						var scripts = document.getElementsByClassName('moduletable')[cpt0].getElementsByTagName('script');
						for(var i=0; i < scripts.length;i++) {
							if (window.execScript) {
								window.execScript(scripts[i].text.replace('<!--',''));
							} else {
								window.eval(scripts[i].text);
							}
						}

						cpt0++;
					}
					taillerEtiquette();
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
			document.getElementById('sortable').style.display = "none";
			document.getElementById('validation').style.display = "block";
			document.getElementsByClassName('filter')[0].parentNode.parentNode.style.display = "none";
			document.getElementsByClassName('btnExport')[0].style.display = "none";
			button.value = "<?php echo JText::_('CANCEL'); ?>";
			button.classList.replace("btnQueryBuilder", "btnExport");
			button.style.float = "left";
		} else {
			elt.style.display = "none";
			button.value = "<?php echo JText::_('MOD_EMUNDUS_QUERY_BUILDER_CREATE_MODULE'); ?>";
			button.classList.replace("btnExport", "btnQueryBuilder");
			document.getElementById('sortable').style.display = "block";
			document.getElementById('validation').style.display = "none";
			document.getElementsByClassName('filter')[0].parentNode.parentNode.style.display = "block";
			document.getElementsByClassName('btnExport')[0].style.display = "block";
			button.style.float = "right";
		}
	}

	// Create user-made stat module
	function createModule() {
		if (document.getElementById("titleModule").value != "" && document.getElementById("typeModule").value != "" && document.getElementById("indicateurModule").value != "" && document.getElementById("axeXModule").value != "" && document.getElementById("axeYModule").value != "") {
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
					idMenu: <?= JFactory::getApplication()->getMenu()->getActive()->id; ?>
				},
				success : function(data) {
					if(data.success) {
						msg = JSON.parse(data.data);
						if (msg.status) {
							window.location.assign("<?= basename($_SERVER['REQUEST_URI']); ?>");
						} else {
							console.log(msg.msg);
						}
					} else {
						document.getElementById('errorCreateModule').innerHTML = "<?= JText::_('MOD_EMUNDUS_QUERY_BUILDER_ERROR_CREATE_MODULE_2'); ?>";
					}
				}
			});
		} else {
			document.getElementById('errorCreateModule').innerHTML = "<?= JText::_('MOD_EMUNDUS_QUERY_BUILDER_ERROR_CREATE_MODULE'); ?>";
		}
	}

	// Display or not the chosen stat module
	function changePublished(idModule) {
		jQuery.ajax({
			type : "POST",
			url : "index.php?option=com_ajax&module=emundus_query_builder&method=changePublishedModule&format=json",
			async: true,
			cache: false,
			data : { idChangePublishedModule: idModule },
			success : function(data) {
				msg = JSON.parse(data.data);
				if (msg.status) {
					window.location.assign("<?= basename($_SERVER['REQUEST_URI']); ?>");
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
			cancelButtonText: '<?php echo JText::_("CANCEL"); ?>',
			showCancelButton: true,
			progressSteps: ['1', '2']
		}).queue([
			{
				title: "<?php echo JText::_('MOD_EMUNDUS_QUERY_BUILDER_TITLE_MODULE'); ?>",
				inputValue: ''+titleModule,
				confirmButtonText: '<?php echo JText::_("MOD_EMUNDUS_QUERY_BUILDER_NEXT"); ?> &rarr;',
			},
			{
				title: "<?= JText::_('MOD_EMUNDUS_QUERY_BUILDER_TYPE_MODULE'); ?>",
				input: 'select',
				inputOptions: {
					timeseries: '<?= JText::_("MOD_EMUNDUS_QUERY_BUILDER_LINE_TIME_LABEL") ?>',
					column2d: '<?= JText::_("MOD_EMUNDUS_QUERY_BUILDER_COLUMN_LABEL") ?>',
					column3d: '<?= JText::_("MOD_EMUNDUS_QUERY_BUILDER_COLUMN_3D_LABEL") ?>',
					scrollcolumn2d: '<?= JText::_("MOD_EMUNDUS_QUERY_BUILDER_COLUMN_SCROLL_LABEL") ?>',
					line: '<?= JText::_("MOD_EMUNDUS_QUERY_BUILDER_LINE_LABEL") ?>',
					scrollline2d: '<?= JText::_("MOD_EMUNDUS_QUERY_BUILDER_LINE_SCROLL_LABEL") ?>',
					area2d: '<?= JText::_("MOD_EMUNDUS_QUERY_BUILDER_AREA_LABEL") ?>',
					scrollarea2d: '<?= JText::_("MOD_EMUNDUS_QUERY_BUILDER_AREA_SCROLL_LABEL") ?>',
					bar2d: '<?= JText::_("MOD_EMUNDUS_QUERY_BUILDER_BAR_LABEL") ?>',
					bar3d: '<?= JText::_("MOD_EMUNDUS_QUERY_BUILDER_BAR_3D_LABEL") ?>',
					scrollbar2d: '<?= JText::_("MOD_EMUNDUS_QUERY_BUILDER_BAR_SCROLL_LABEL") ?>',
					pie2d: '<?= JText::_("MOD_EMUNDUS_QUERY_BUILDER_PIE_LABEL") ?>',
					pie3d: '<?= JText::_("MOD_EMUNDUS_QUERY_BUILDER_PIE_3D_LABEL") ?>',
					doughnut2d: '<?= JText::_("MOD_EMUNDUS_QUERY_BUILDER_DOUGHNUT_LABEL") ?>',
					doughnut3d: '<?= JText::_("MOD_EMUNDUS_QUERY_BUILDER_DOUGHNUT_3D_LABEL") ?>',
					pareto2d: '<?= JText::_("MOD_EMUNDUS_QUERY_BUILDER_PARETO_LABEL") ?>',
					pareto3d: '<?= JText::_("MOD_EMUNDUS_QUERY_BUILDER_PARETO_3D_LABEL") ?>'
				},
				inputValue: typeModule,
				confirmButtonText: '<?php echo JText::_("MOD_EMUNDUS_QUERY_BUILDER_VALIDATION"); ?>',
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
							window.location.assign("<?= basename($_SERVER['REQUEST_URI']); ?>");
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
			title: "<?= JText::_('ASK'); ?>",
			text: "<?= JText::_('WARNING'); ?>",
			icon: "warning",
			showCancelButton: true,
			confirmButtonText: '<?= JText::_("ANSWER_2"); ?>',
			cancelButtonText: '<?= JText::_("ANSWER_1"); ?>',
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
							window.location.assign("<?= basename($_SERVER['REQUEST_URI']); ?>");
						} else {
							console.log(msg.msg);
						}
					}
				});
			} else {
				Swal.fire("<?= JText::_('CANCEL'); ?>", "<?= JText::_('CANCEL_MESSAGE'); ?>", "error");
			}
		})
	}
</script>
