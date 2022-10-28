window.addEvent('fabrik.loaded', function () {
	Fabrik.addEvent('fabrik.form.autofill.update.end', function (form, json) {
		form.form.doSubmit(new Event.Mock(form.form._getButton('Submit')), form.form._getButton('Submit'));
	});
	
	Fabrik.addEvent('fabrik.form.autofill.update.start', function (form, json) {
		//form.json['fab_autocomplete___foo_fill_raw'] = "changed!";
	});
});
