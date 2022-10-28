(function (document, $) {
    var newMemberAdded = 0;
    var numberMembers = Joomla.getOptions('numberMembers');

    setRecalculateFee = (function () {
        $('#re_calculate_fee').prop('checked', true);
    });

    addGroupMember = (function () {
        if (newMemberAdded < 4) {
            newMemberAdded++;
            $('input[name=number_registrants]').val(newMemberAdded + numberMembers);
            var newMemberContainerId = 'group_member_' + (newMemberAdded + numberMembers);
            $('#' + newMemberContainerId).show();

            $('#re_calculate_fee').prop('checked', true);
        } else {
            alert(Joomla.JText._('EB_ADD_MEMBER_MAXIMUM_WARNING'));
        }
    });

    removeGroupMember = (function (memberId) {
        if (memberId == 0) {
            var newMemberContainerId = 'group_member_' + (newMemberAdded + numberMembers);
            $('#' + newMemberContainerId).hide();
            newMemberAdded--;
            $('input[name=number_registrants]').val(newMemberAdded + numberMembers);
            $('#re_calculate_fee').prop('checked', true);
        } else {
            if (confirm(Joomla.JText._('EB_REMOVE_EXISTING_MEMBER_CONFIRM'))) {
                var form = document.adminForm;
                form.group_member_id.value = memberId;
                form.task.value = 'registrant.remove_group_member';
                form.submit();
            }
        }
    });

    populateRegistrantData = (function () {
        var userId = $('#user_id_id').val();
        var eventId = $('#event_id').val();
        $.ajax({
            type: 'GET',
            url: 'index.php?option=com_eventbooking&task=get_profile_data&user_id=' + userId + '&event_id=' + eventId,
            dataType: 'json',
            success: function (json) {
                var selecteds = [];
                for (var field in json) {
                    value = json[field];
                    if ($("input[name='" + field + "[]']").length) {
                        //This is a checkbox or multiple select
                        if ($.isArray(value)) {
                            selecteds = value;
                        } else {
                            selecteds.push(value);
                        }
                        $("input[name='" + field + "[]']").val(selecteds);
                    } else if ($("input[type='radio'][name='" + field + "']").length) {
                        $("input[name=" + field + "][value=" + value + "]").attr('checked', 'checked');
                    } else {
                        $('#' + field).val(value);
                    }
                }
            }
        })
    });

    Joomla.submitbutton = function (pressbutton) {
        if (pressbutton === 'refund') {

            if (confirm(Joomla.JText._('EB_REFUND_REGISTRANT_CONFIRM'))) {
                Joomla.submitform(pressbutton);
            }

            return;
        }
        Joomla.submitform(pressbutton);
    };

    $(document).ready(function () {
        buildStateFields('state', 'country', Joomla.getOptions('selectedState'));
    });
})(document, jQuery);