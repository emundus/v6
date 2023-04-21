<?php
/**
 * @version		$Id: data.php 14401 2016-05-26 14:10:00Z brivalland $
 * @package		Joomla
 * @subpackage	Emundus
 * @copyright	Copyright (C) 2016 eMundus. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<div class="match example">
<form class="ui form">
  <div class="two fields">
  	<div class="field">
	    <label><?php echo JText::_('COM_EMUNDUS_AMETYS_SCHOOLYEAR'); ?></label>
	    <input id="year" type="text" placeholder="<?php echo date('Y'); ?>" value="<?php echo date('Y'); ?>">
	</div>
	<div class="field">
	    <label><?php echo JText::_('COM_EMUNDUS_PROFILE'); ?></label>
	    <select id="profile_id" class="ui dropdown">
	  	<?php
	  	foreach ($this->applicantProfiles as $key => $value) {
	  		echo '<option value="'.$value->id.'">'.$value->label.'</option>';
	  	}
	    ?>
	    </select>
	</div>
  </div>

  <div class="field">
    <label><?php echo JText::_('COM_EMUNDUS_ATTACHMENTS_SHORT_DESC'); ?></label>
    <textarea id='short_description'></textarea>
  </div>

  <div class="two fields">
  	<div class="field">
	  	<label><?php echo JText::_('COM_EMUNDUS_AMETYS_CAMPAIGN_START_DATE'); ?></label>
	     <div class="ui input left icon">
	      <i class="calendar icon"></i>
	      <input type="text" id="start_date" name="daterange" class="form-control">
	    </div>
	</div>
	<div class="field">
	    <label><?php echo JText::_('COM_EMUNDUS_AMETYS_CAMPAIGN_END_DATE'); ?></label>
	     <div class="ui input left icon">
	      <i class="calendar icon"></i>
	      <input type="text" id="end_date" name="daterange" class="form-control">
	    </div>
    </div>
  </div>

  <div id="submit_campaign" class="ui orange submit button">
  	<?php echo JText::_('SUBMIT'); ?>
  </div>
  <div class="ui error message"></div>
</form>
</div>

<script type="text/javascript">

$( document ).ready(function() {
	$( "#submit_campaign" ).click(function() {
		var start_date = document.getElementById('start_date').value;
		var end_date = document.getElementById('end_date').value;
		var profile_id = document.getElementById('profile_id').value;
		var year = document.getElementById('year').value;
		var short_description = document.getElementById('short_description').value;

	  if (start_date=="" || end_date=="" || profile_id=="" || year=="") {
	  	alert(Joomla.JText._("COM_EMUNDUS_ERROR_MISSING_FORM_DATA"));

	  	return false;
	  } else {
	  	$.ajax({
	        type:'POST',
	        url:'index.php?option=com_emundus&controller=campaign&task=addcampaigns&Itemid=',
	        dataType:'json',
	        data: ({
	        	start_date: start_date,
	        	end_date: end_date,
	        	profile_id: profile_id,
	        	year: year,
	        	short_description: short_description
	       	}),
	        success: function(result)
	        {
	        	if (result.status)
	            {
	            	$('#em-content').empty();
		           	$('#em-content').append("<hr>");
		        	$('#em-content').append(result.msg);
	            }
	        },
	        error: function (jqXHR, textStatus, errorThrown)
	        {
	        	$('#em-content').empty();
		        $('#em-content').append("<hr>");
		       	$('#em-content').append(result.msg);
		       	$('#em-content').append(jqXHR.responseText);
	            console.log(jqXHR.responseText);
	        }
	    })
	  }
	});

    $('select.dropdown')
	  .dropdown()
	;

	$('#start_date').daterangepicker({
	    "showDropdowns": true,
	    "showWeekNumbers": true,
	    "showISOWeekNumbers": true,
	    "timePicker": true,
	    "timePicker24Hour": true,
	    "timePickerSeconds": true,
	    "autoApply": true,
	    "alwaysShowCalendars": true,
	    "drops": "up",
	    locale: {
            format: 'YYYY-MM-DD h:mm'
        }
	}, function(start, end, label) {
	  		document.getElementById('start_date').value = start.format('YYYY-MM-DD h:mm');
	    	document.getElementById('end_date').value = end.format('YYYY-MM-DD h:mm');
	});

	$('#end_date').daterangepicker({
	    "showDropdowns": true,
	    "showWeekNumbers": true,
	    "showISOWeekNumbers": true,
	    "timePicker": true,
	    "timePicker24Hour": true,
	    "timePickerSeconds": true,
	    "autoApply": true,
	    "alwaysShowCalendars": true,
	    "drops": "up",
	    locale: {
            format: 'YYYY-MM-DD h:mm'
        }
	}, function(start, end, label) {
	  		document.getElementById('start_date').value = start.format('YYYY-MM-DD h:mm');
	    	document.getElementById('end_date').value = end.format('YYYY-MM-DD h:mm');
	});

});

</script>
