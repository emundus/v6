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
  	<a href="#" onClick="syncAmetysProgramme();" id="em-sync">
  		<i class="circular massive refresh link icon"></i> <?php echo JText::_('COM_EMUNDUS_SYNC_AMETYS_PROGRAMMES'); ?> 
  	</a>
  </div>
  <div class="five wide column">
  	<a href="#" id="em-new-campaign">
  		<i class="circular massive add circle link icon"></i> <?php echo JText::_('COM_EMUNDUS_DECLARE_NEW_CAMPAIGN'); ?> 
  	</a>
  </div>
  <div class="five wide column">
  	<a href="#" id="em-ametys-cart">
  		<i class="circular massive in cart link icon"></i> <?php echo JText::_('COM_EMUNDUS_DISPLAY_AMETYS_CART'); ?> 
  	</a>
  </div>
  <div class="sixteen wide column">
  	<div id="em-content"></div>
  </div>
</div>

<script type="text/javascript">
	function syncAmetysProgramme()
	{ 
		$('#em-content').empty();
		$('#em-sync i').attr('class','circular massive refresh loading icon');
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

	            $('#em-content').append(data);
	            $('#em-sync i').attr('class','circular massive refresh link icon');
	        },
	        error: function (jqXHR, textStatus, errorThrown)
	        {
	            console.log(jqXHR.responseText);
	        }
	    })
	}

	function syncEmundusProgramme()
	{ 
		//$('#em-content').empty();
		//$('#em-sync i').attr('class','circular massive refresh loading icon');
	    $.ajax({
	        type:'get',
	        url:'index.php?option=com_emundus&controller=programme&task=getprogrammes&Itemid=',
	        dataType:'json',
	        success: function(result)
	        { 
	        	var msg  = result.msg;
	            var data = result.data;
	            if (result.status)
	            {
	                
	               return data;
	            } 
	            else {
	            	return false;
	            }

	            //$('#em-content').append(data);
	            //$('#em-sync i').attr('class','circular massive refresh link icon');
	        },
	        error: function (jqXHR, textStatus, errorThrown)
	        {
	            console.log(jqXHR.responseText);
	        }
	    })
	}
</script>