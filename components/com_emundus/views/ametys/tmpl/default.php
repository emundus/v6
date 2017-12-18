<?php
/**
 * @version		$Id: default.php 14401 2016-05-16 14:10:00Z brivalland $
 * @package		Joomla
 * @subpackage	Emundus
 * @copyright	Copyright (C) 2016 emundus.fr. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
JHTML::_('behavior.tooltip');
?>

<div class="ui grid">
  <div class="five wide column">
  	<a href="#" onClick="sync();" id="em-sync">
  		<i class="circular big refresh link icon"></i> <?php echo JText::_('COM_EMUNDUS_SYNC_AMETYS_PROGRAMMES'); ?> 
  	</a>
  </div>
  <div class="five wide column">
  	<a href="#" onClick="getFormCampaign();" id="em-new-campaign">
  		<i class="circular big add circle link icon"></i> <?php echo JText::_('COM_EMUNDUS_DECLARE_NEW_CAMPAIGN'); ?> 
  	</a>
  </div>
  <div class="five wide column">
  	<!--<a href="#" id="em-ametys-cart">
  		<i class="circular big in cart link icon"></i> <?php echo JText::_('COM_EMUNDUS_DISPLAY_AMETYS_CART'); ?> 
  	</a>-->
  </div>
  <div class="sixteen wide column">
  	<div id="em-content"></div>
  </div>
</div>

<script type="text/javascript">

	function getFormCampaign()
	{ 
		$('#em-content').empty();
		$('#em-new-campaign i').attr('class','circular big add circle loading icon');
	    $.ajax({
	        type:'get',
	        url:'index.php?option=com_emundus&view=ametys&layout=formcampaign&format=raw&Itemid=',
	        dataType:'html',
	        success: function(result)
	        { 
	        	$('#em-new-campaign i').attr('class','circular big add circle link icon');
	        	$('#em-content').append(result);          
	        }, 
	        error: function (jqXHR, textStatus, errorThrown)
	        {
	            console.log(jqXHR.responseText);
	        }
	    })
	}

	function syncAmetysProgramme()
	{ 
		$('#em-content').empty();
		$('#em-sync i').attr('class','circular big refresh loading icon');
	    $.ajax({
	        type:'get',
	        url:'index.php?option=com_emundus&controller=ametys&task=getprogrammes&Itemid=',
	        dataType:'json',
	        success: function(result)
	        { 
	        	var msg  = result.msg;
	            var data = result.data;
	            if (result.status)
	            {
	                
	               for (var d in data)
	                {
	                    if (typeof(data[d].cdmCode)  == "undefined" || typeof(data[d].title) == "undefined")
	                        break;

	                    $('#em-content').append(data[d].title);
	                    $('#em-content').append(" [" + data[d].cdmCode + "]");
	                    $('#em-content').append("<br>");   
	                }
	            }

	            $('#em-sync i').attr('class','circular big refresh link icon');
	        }, 
	        error: function (jqXHR, textStatus, errorThrown)
	        {
	            console.log(jqXHR.responseText);
	        }
	    })
	}

	function syncEmundusProgramme()
	{ 
		var result = $.ajax({
	        type:'get',
	        url:'index.php?option=com_emundus&controller=programme&task=getprogrammes&Itemid=',
	        dataType:'json',
	        success: function(result)
	        { 
	        	var msg  = result.msg;
	            var data = result.data;
	            if (result.status)
	            {
	               for (var d in data)
	                {
	                    if (typeof(data[d].code)  == "undefined" || typeof(data[d].label) == "undefined")
	                        break;

	                    $('#em-content').append(data[d].label);
	                    $('#em-content').append(" [" + data[d].code + "]");
	                    $('#em-content').append("<br>");
	                    
	                }
	            } 
	            else {
	            	$('#em-content').append("<hr>");
	            	$('#em-content').append(Joomla.JText._("COM_EMUNDUS_CANNOT_RETRIEVE_EMUNDUS_PROGRAMME_LIST"));
	            }
	        },
	        error: function (jqXHR, textStatus, errorThrown)
	        {
	            console.log(jqXHR.responseText);
	        }
	    });
		return result;
	}

	function sync(){
		$('#em-content').empty();
		$('#em-sync i').attr('class','circular big refresh loading icon');

		var programme = {};

		$.when(

			$.get('index.php?option=com_emundus&controller=ametys&task=getprogrammes&Itemid=', function(ametys){
				$('#em-content').append("<hr>");
				$('#em-content').append(Joomla.JText._("COM_EMUNDUS_RETRIEVE_AMETYS_STORED_PROGRAMMES"));
				programme.ametys = ametys;
			}),

			$.get('index.php?option=com_emundus&controller=programme&task=getprogrammes&Itemid=', function(emundus){
				$('#em-content').append("<hr>");
				$('#em-content').append(Joomla.JText._("COM_EMUNDUS_RETRIEVE_EMUNDUS_STORED_PROGRAMMES"));
				programme.emundus = emundus;
			})

		).done(function(ametys, emundus) {
			var ametys = JSON.parse(programme.ametys);
			var emundus = JSON.parse(programme.emundus);

            if (ametys.status) {
	           var ametysData = ametys.data;
	           var emundusData = emundus.data;
			   var data_to_add = new Array();
			   var data_to_update = new Array();
               var stored = false;
			  
			   $('#em-content').append("<hr>");
			   $('#em-content').append(Joomla.JText._("COM_EMUNDUS_COMPARE_DATA"));
			   $('#em-content').append("<hr>");
			   $('#em-content').append(Joomla.JText._("COM_EMUNDUS_DATA_TO_ADD"));
			   $('#em-content').append(" : <br>");

			   var to_edit=0;
               for (var d in ametysData) {
                    if (typeof(ametysData[d].cdmCode)  == "undefined" || typeof(ametysData[d].title) == "undefined")
                        break;
               	
               		stored = false;
               		for (var e in emundusData) {

               			if (emundusData[e].code == ametysData[d].cdmCode) {
               				stored = true;
               			}
               		}  		
               		
               		if (!stored) {
               			// convert Ametys database definition to emundus database definition
               			var emData = new Object();
               			
               			emData.code = ametysData[d].cdmCode;
               			emData.label = ametysData[d].title + " { " + ametysData[d].organisation + " }";
               			emData.organisation = ametysData[d].organisation;
               			emData.organisation_code = ametysData[d].organisation_code;
               			emData.notes = ametysData[d].presentation;
               			emData.published = 1;
               			emData.programmes = ametysData[d].catalog;
               			emData.synthesis = "<?php echo $this->ametys_sync_default_synthesis; ?>";
               			emData.fabrik_group_id = "<?php echo $this->ametys_sync_default_eval; ?>";
               			emData.fabrik_decision_group_id = "<?php echo $this->ametys_sync_default_decision; ?>";
               			emData.apply_online = 1;
               			emData.ordering = d;
               			emData.url = ametysData[d].programWebSiteUrl;

               			data_to_add.push(emData);
               			$('#em-content').append("<i class='add circle icon'></i> ");
	                    $('#em-content').append(emData.label);
	                    $('#em-content').append(" [" + emData.code + "]");
	                    $('#em-content').append("<br>");
	                }
	                else {
	                	if (to_edit == 0) {
	                		$('#em-content').append("<hr>");
			   				$('#em-content').append(Joomla.JText._("COM_EMUNDUS_DATA_TO_EDIT"));
			   				to_edit = 1;
	                	};
               			// convert Ametys database definition to emundus database definition
               			var emData = new Object();

               			emData.code = ametysData[d].cdmCode;
               			emData.label = ametysData[d].title + " { " + ametysData[d].organisation + " }";
               			emData.organisation = ametysData[d].organisation;
               			emData.organisation_code = ametysData[d].organisation_code;
               			emData.notes = ametysData[d].presentation;
               			emData.published = 1;
               			emData.programmes = ametysData[d].catalog;
               			emData.synthesis = "<?php echo $this->ametys_sync_default_synthesis; ?>";
               			emData.fabrik_group_id = "<?php echo $this->ametys_sync_default_eval; ?>";
               			emData.fabrik_decision_group_id = "<?php echo $this->ametys_sync_default_decision; ?>";
               			emData.apply_online = 1;
               			emData.ordering = d;
               			emData.url = ametysData[d].programWebSiteUrl;

               			data_to_update.push(emData);
               			/*$('#em-content').append("<i class='edit circle icon'></i> ");
	                    $('#em-content').append(emData.label);
	                    $('#em-content').append(" [" + emData.code + "]");
	                    $('#em-content').append("<br>");*/
	                }
                }

                if (data_to_update.length > 0) {
					$('#em-content').append("<hr>");
					$('#em-content').append(Joomla.JText._("COM_EMUNDUS_ADD_DATA"));

					$.ajax({
				        type:'POST',
				        url:'index.php?option=com_emundus&controller=programme&task=editprogrammes&Itemid=',
				        dataType:'json',
				        data: {data : data_to_update},
				        success: function(result)
				        { 
				        	if (result.status)
				            {
					           	$('#em-content').append("<hr>");
					        	$('#em-content').append(Joomla.JText._("COM_EMUNDUS_SYNC_DONE"));				            
				            }
				        }, 
				        error: function (jqXHR, textStatus, errorThrown)
				        {
				            console.log(jqXHR.responseText);
				        }
				    });
				}

                if (data_to_add.length > 0) {
					$('#em-content').append("<hr>");
					$('#em-content').append(Joomla.JText._("COM_EMUNDUS_ADD_DATA"));

					$.ajax({
				        type:'POST',
				        url:'index.php?option=com_emundus&controller=programme&task=addprogrammes&Itemid=',
				        dataType:'json',
				        data: {data : data_to_add},
				        success: function(result)
				        { 
				        	if (result.status)
				            {
					           	$('#em-content').append("<hr>");
					        	$('#em-content').append(Joomla.JText._("COM_EMUNDUS_SYNC_DONE"));				            
				            }
				        }, 
				        error: function (jqXHR, textStatus, errorThrown)
				        {
				            console.log(jqXHR.responseText);
				        }
				    });

				    $.ajax({
				        type:'POST',
				        url:'index.php?option=com_emundus&controller=groups&task=addgroups&Itemid=',
				        dataType:'json',
				        data: {data : data_to_add},
				        success: function(result)
				        { 
				        	if (result.status)
				            {
					           	$('#em-content').append("<hr>");
					        	$('#em-content').append(Joomla.JText._("COM_EMUNDUS_GROUPS_SYNC_DONE"));				            
				            }
				        }, 
				        error: function (jqXHR, textStatus, errorThrown)
				        {
				            console.log(jqXHR.responseText);
				        }
				    });


                } else {
                	$('#em-content').append("<hr>");
					$('#em-content').append(Joomla.JText._("COM_EMUNDUS_NO_SYNC_NEEDED"));
                }

	        	$('#em-sync i').attr('class','circular big refresh link icon');
            }
            else {
            	$('#em-content').append("<hr>");
            	$('#em-content').append(Joomla.JText._("COM_EMUNDUS_CANNOT_RETRIEVE_EMUNDUS_PROGRAMME_LIST"));
            }
		});
	}
</script>