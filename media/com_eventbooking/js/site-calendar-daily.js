(function (document) {
    gotoDate = function () {
        var url = Joomla.getOptions('dailyCalendarUrl');
        date = document.getElementById('date');

        if (date.value) {
            location.href = url + date.value;
        } else {
            alert(Joomla.JText._('EB_PLEASE_CHOOSE_DATE'));
        }
    }
})(document);