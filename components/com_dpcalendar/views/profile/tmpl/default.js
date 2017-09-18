jQuery(document).ready(function(){
	jQuery('#read-users, #write-users').chosen({
		placeholder_text_multiple: Joomla.JText._('COM_DPCALENDAR_VIEW_DAVCALENDAR_NONE_SELECTED_LABEL')
	}).change(function(event, obj){
		var data = obj;
		data['action'] = jQuery(event.target).attr('id') == 'write-users' ? 'write' : 'read';
		data[jQuery('#token').val()] = 1;
		jQuery.ajax({
			type: 'post',
			url: 'index.php?option=com_dpcalendar&view=profile&task=profile.change',
			data: data,
			success: function (response) {
			}
		});
	});
});