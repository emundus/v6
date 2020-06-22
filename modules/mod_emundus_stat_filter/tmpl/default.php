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
	<div class="filter">
		<form action="" method="" onsubmit="return false;">
			<label><?php echo JText::_('PROGRAM'); ?></label>
			<select name="prog" id="progFilter" onchange="progAction()">
				<option value="-1"></option>
				<?php
				foreach ($tabProg as $prog) { 
					echo "<option value=\"".$prog['code']."\" ".(($array["prog"]===$prog['code'])?"selected":"").">".$prog['label']."</option>";
				} ?>
			</select>
			
			<label><?php echo JText::_('YEARS_CAMPAIGN'); ?></label>
			<select name="years" id="yearsFilter" onchange="yearAction()">
				<option value="-1"></option>
				<?php
				foreach ($tabYear as $year) { 
					echo "<option value=\"".$year['year']."\"".(($array["year"]===$year['year'])?"selected":"").">".$year['year']."</option>";
				} ?>
			</select>
			
			<label><?php echo JText::_('CAMPAIGN'); ?></label>
			<select name="campaign" id="campaignFilter" onchange="campaignAction()">
				<option value="-1"></option>
				<?php
				foreach ($tabCampaign as $campaign) { 
					echo "<option value=\"".$campaign['id']."\"".(($array["campaign"]===$campaign['id'])?"selected":"").">".$campaign['label']."</option>";
				} ?>
			</select>
		</form>
	</div>
</center>

<script>
	// Allows the display in the filter of years and campaigns associated with the chosen program
	function progAction() {
		jQuery.ajax({
			url: "index.php?option=com_ajax&module=emundus_stat_filter&format=json", 
			type: "POST",
			async: true,
			cache: false,
			data: {
				prog : document.getElementById("progFilter").value,
				year : -2,
				campaign : -2
			},
			success: function(response){
				msg = JSON.parse(response.data);
				if (msg.status) {
					document.getElementById("yearsFilter").innerHTML = msg.msg.split("////")[1];
					document.getElementById("campaignFilter").innerHTML = msg.msg.split("////")[2];
					fusioncharts = new Array();
					refreshModuleGraph();
				} else {
					console.log(msg.msg);
				}
			}
		});
	}
	
	// Allows the display in the filter of programs and campaigns associated with the chosen year
	function yearAction() {
		jQuery.ajax({
			url: "index.php?option=com_ajax&module=emundus_stat_filter&format=json", 
			type: "POST",
			async: true,
			cache: false,
			data: {
				prog : -2,
				year : document.getElementById("yearsFilter").value,
				campaign : -2
			},
			success: function(response){
				msg = JSON.parse(response.data);
				if (msg.status) {
					document.getElementById("progFilter").innerHTML = msg.msg.split("////")[0];
					document.getElementById("campaignFilter").innerHTML = msg.msg.split("////")[2];
					fusioncharts = new Array();
					refreshModuleGraph();
				} else {
					console.log(msg.msg);
				}

			}
		});
	}
	
	// Allows the display in the filter of programs and years associated with the chosen campaign
	function campaignAction() {
		jQuery.ajax({
			url: "index.php?option=com_ajax&module=emundus_stat_filter&format=json", 
			type: "POST",
         cache:false,
         async: false,
			data: {
				prog : -2,
				year : -2,
				campaign : document.getElementById("campaignFilter").value
			},
			success: function(response){
				msg = JSON.parse(response.data);
				if (msg.status) {
					document.getElementById("progFilter").innerHTML = msg.msg.split("////")[0];
					document.getElementById("yearsFilter").innerHTML = msg.msg.split("////")[1];
					fusioncharts = new Array();
					refreshModuleGraph();
				} else {
					console.log(msg.msg);
				}
			}
		});
	}
	
	// Allows you to refresh the stats modules dynamically
	function refreshModuleGraph() {
		jQuery.ajax({
			type: 'POST',
			url: 'index.php?option=com_ajax&module=emundus_stat_filter&method=reloadModule&format=json',
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
						document.getElementsByClassName('moduletable')[cpt0].getElementsByClassName(modulesString[cpt]).length <= 0 ; i++) {
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
					
					// if(fusioncharts != undefined) {
						// for(var cpt = 0 ; cpt < fusioncharts.length ; cpt++)
							// fusioncharts[cpt].render();
					// }
				
				} else {
					console.log(msg.msg);
				}
			}
		});
	}
</script>