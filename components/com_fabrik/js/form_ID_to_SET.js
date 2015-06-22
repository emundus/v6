
Fabrik.addEvent('fabrik.form.submit.start', function(form, event, button) {
    alert('aha! you really should not press that button');
    form.result = false;
});
