(function (document, $) {
    $(document).ready(function () {
        $.ajax({
            type: 'POST',
            url: 'index.php?option=com_eventbooking&task=check_update',
            dataType: 'json',
            success: function (msg, textStatus, xhr) {
                var updateCheck = $('#update-check');
                if (msg.status == 1) {
                    updateCheck.find('img').attr('src', Joomla.getOptions('upToDateImg')).attr('title', msg.message);
                    updateCheck.find('span').text(msg.message);
                } else if (msg.status == 2) {
                    updateCheck.find('img').attr('src', Joomla.getOptions('updateFoundImg')).attr('title', msg.message);
                    updateCheck.find('a').attr('href', 'index.php?option=com_installer&view=update');
                    updateCheck.find('span').text(msg.message);
                } else {
                    updateCheck.find('img').attr('src', Joomla.getOptions('errorFoundImg'));
                    updateCheck.find('span').text(Joomla.JText._('EB_UPDATE_CHECKING_ERROR'));
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                var updateCheck = $('#update-check');
                updateCheck.find('img').attr('src', Joomla.getOptions('errorFoundImg'));
                updateCheck.find('span').text(Joomla.JText._('EB_UPDATE_CHECKING_ERROR'));
            }
        });
    });
})(document, jQuery);