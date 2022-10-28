function doButton(el) {
	var url = "index.php?option=com_fabrik&format=raw&task=plugin.userAjax&method=doButton";
	new Request({url:url,
		onComplete: function(response) {
			if (response != '') {
				alert(response);
			}
		}
	}).send();
}

requirejs(['fab/fabrik'], function () {
	var form = Fabrik.getBlock('form_2', false, function(form) {
		var name = form.elements.get('fab_main_test___name');
		alert(name);
	});

	Fabrik.addEvent('fabrik.form.loaded', function (form) {
		form.addElementFX('fabrik_trigger_group_group78', 'slide out');
	});

	Fabrik.addEvent('fabrik.window.close', function(w) {
		fconsole('closed');
	});
});

function doCheckbox(el)
{
    if (jQuery(event.target).is(":checked") === true) {
        var group = "input:checkbox[name^='" + el.baseElementId + "']";
        jQuery(group).attr("checked", false);
        jQuery(event.target).attr("checked", true);
    } else {
        jQuery(event.target).is(":checked", false);
    }
}