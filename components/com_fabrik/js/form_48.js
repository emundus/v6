requirejs(['fab/fabrik'], function () {
	Fabrik.addEvent('fabrik.form.elements.added', function (form) {
		fconsole('form elements added for: ' + form.block);
	});
});