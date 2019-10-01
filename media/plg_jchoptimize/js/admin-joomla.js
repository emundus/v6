//function submitJchSettings()
//{
//        jQuery("#config_edit_form").submit();
//}
//;
//
//jQuery(document).ready(function () {
//        jQuery(".chzn-custom-value").chosen({width: "92%"});
//        
//        jQuery('.collapsible').collapsible();
//});

                
function getSelector(int, state)
{
        return "fieldset.s" + int + "-" + state + " > input[type=radio]";
}
;

(function( $ ) {
	$( document ).ready(function() {

		var timestamp = getTimeStamp();
		var datas = [];
		//Get all the multiple select fields and iterate through each
		$('select[multiple=multiple]').each(function(){
			var el = $(this);

			datas.push({'id': el.attr('id'),'type':el.attr('data-jch_type'), 'param': el.attr('data-jch_param'), 'group': el.attr('data-jch_group')});

		});

		var xhr = jQuery.ajax({
			dataType: 'json',
			url: jch_ajax_url + "&action=getmultiselect&_=" + timestamp,
			data: {'data': datas},
			method: 'POST',
			success: function (response) {
				$.each(response.data, function(id, obj){

					$.each(obj.data, function(value, option){
						$('#' + id).append('<option value="' + value + '">' + option + '</option>');
					});
				
					$('#' + id).trigger("liszt:updated");
					
					//Get name of field's param saved in attribute
					var field = $('#' + id).attr('data-jch_param');
					//remove loading image
					$('img#img-' + field).remove();
					//append 'Add item' button'
					$('div#div-' + field).append('<button type="button" class="btn" onclick="addJchOption(\'' + id + '\')">Add item</button>');
				});
			},
			error: function (jqXHR, textStatus, errorThrown) {
				console.error('Error returned from ajax function \'getmultiselect\'');
				console.error('textStatus: ' + textStatus);
				console.error('errorThrown: ' + errorThrown);
			}

		});
	});
})( jQuery );
