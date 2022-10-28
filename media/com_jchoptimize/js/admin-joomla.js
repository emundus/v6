-/**
 * jch optimize - performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */


//function getSelector(int, state) {
//    return "fieldset.s" + int + "-" + state + " > input[type=radio]";
//}
//;

(function ($) {
    $(document).ready(function () {

        var timestamp = getTimeStamp();
        var datas = [];
        //Get all the multiple select fields and iterate through each
        $('select.jch-exclude').each(function () {
            var el = $(this);

            datas.push({
                'id': el.attr('id'),
                'type': el.attr('data-jch_type'),
                'param': el.attr('data-jch_param'),
                'group': el.attr('data-jch_group')
            });

        });

        var xhr = jQuery.ajax({
            dataType: 'json',
            url: jch_ajax_url + '&task=multiselect&_=' + timestamp,
            data: {'data': datas},
            method: 'POST',
            success: function (response) {
                $.each(response.data, function (id, obj) {

                    $.each(obj.data, function (value, option) {
                        $('#' + id).append('<option value="' + value + '">' + option + '</option>');
                    });

                    $('#' + id).trigger("liszt:updated");
                    $('#' + id).trigger("chosen:updated");

                    //Get name of field's param saved in attribute
                    var field = $('#' + id).attr('data-jch_param');
                    //remove loading image
                    $('img#img-' + field).remove();
                    //append 'Add item' button'
                    $('div#div-' + field).append('<button type="button" class="btn btn-sm btn-secondary" onmousedown="addJchOption(\'' + id + '\')">Add item</button>');
                });
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('Error returned from ajax function \'getmultiselect\'');
                console.error('textStatus: ' + textStatus);
                console.error('errorThrown: ' + errorThrown);
                console.warn('response: ' + jqXHR.responseText);
            }

        });
    });


})(jQuery);


function addJchOption(id)
{
    var input = jQuery("#" + id + " + .chzn-container > .chzn-choices > .search-field > input, #" + id + " + .chosen-container > .chosen-choices > .search-field > input");
    var txt = input.val();

    if (txt === input.prop("defaultValue")) {
        txt = null;
    }

    if (txt === null || txt === "") {
        alert("Please input an item in the box to add to the drop-down list");
        return false;
    }

    jQuery("#" + id).append(jQuery("<option/>", {
        value: txt.replace("...", ""),
        text: txt
    }).attr("selected", "selected"));

    jQuery("#" + id).trigger("liszt:updated");
    jQuery("#" + id).trigger("chosen:updated");
}
;

(function ($) {

    $(document).ready(function () {
        if ($('fieldset#jform_pro_smart_combine input:radio:checked').val() === '1') {
            $('button#btn-pro_smart_combine').css('display', 'inline');
        }


        $('fieldset#jform_pro_smart_combine label[for="jform_pro_smart_combine1"]').click(function () {
            if ($(this).attr('class') === 'btn') {

                processSmartCombine();
            }
        });

        $('fieldset#jform_pro_smart_combine label[for="jform_pro_smart_combine0"]').click(function () {
            if ($(this).attr('class') === 'btn') {

                $('#jform_pro_smart_combine_values option').remove();
            }
        });

        $('button#btn-pro_smart_combine').click(function () {
            reprocessSmartCombine();
        })
    });

    function processSmartCombine() {
        $('img#img-pro_smart_combine').css('display', 'inline');
        $('button#btn-pro_smart_combine').css('display', 'none');

        let xhr = $.ajax({
            dataType: 'json',
            url: jch_ajax_url + '&task=smartcombine&_=' + getTimeStamp(),
            method: 'POST',
            success: function (response) {
                const smart_combine_id = '#jform_pro_smart_combine_values';
                $(smart_combine_id + ' option').remove();

                var values = [];

                $.each(response.data.css, function (index, value) {
                    values.push(value);
                });

                $.each(response.data.js, function (index, value) {
                    values.push(value);
                })

                $('#jform_pro_smart_combine_values').val(JSON.stringify(values));

                $('img#img-pro_smart_combine').css('display', 'none');
                $('button#btn-pro_smart_combine').css('display', 'inline');
            }
        })
    }

    function reprocessSmartCombine() {
        processSmartCombine();

        Joomla.submitbutton('config.save.component.apply');
    }
})(jQuery);


function getTimeStamp() {
    return new Date().getTime();
}