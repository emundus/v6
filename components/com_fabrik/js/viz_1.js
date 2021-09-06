/**
 * Created by Hugh on 7/19/2016.
 */
requirejs(['fab/fabrik'], function() {
	Fabrik.addEvent('fabrik.viz.googlemap.info.opened', function(viz, marker) {
		//initModals();
	});
});

jQuery('body').on('click', '.mapButton', function(e) {
	e.preventDefault();
	var rowid = jQuery(e.target).data('rowid');
    var url = Fabrik.liveSite + "/index.php?option=com_fabrik&format=raw&task=plugin.userAjax&method=doButton";
    jQuery.ajax({
        url       : url,
		data      : {
        	rowid: rowid
		}
    }).done(function (r) {
    	alert(r);
    });
});