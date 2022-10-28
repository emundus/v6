requirejs(['fab/fabrik'], function(f) {
    Fabrik.addEvent('fabrik.form.group.delete.end', function(form, event, groupId, repeatCount) {
        // your code here ... update the acle_number with the repeatCount + 1 (it numbers from 0)
        alert('group: ' + groupId + ' repeat: ' + repeatCount);
    });

    Fabrik.addEvent('fabrik.form.group.delete', function(form, event) {
        form.result = confirm('Are you sure?');
    });
});