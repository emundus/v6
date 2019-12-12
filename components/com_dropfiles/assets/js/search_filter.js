function initDatepicker() {
    initDateRange('cfrom', 'cto');
    initDateRange('ufrom', 'uto');

    initDateRange('mod_cfrom', 'mod_cto');
    initDateRange('mod_ufrom', 'mod_uto');

}

function initDateRange(fromId, toId) {
    jQuery("#" + fromId).datetimepicker({
        format: search_date_format,
        onShow: function (ct) {
            this.setOptions({
                maxDate: jQuery("#" + toId).val() ? jQuery("#" + toId).val() : false
            })
        },
        timepicker: false
    });

    jQuery("#" + toId).datetimepicker({
        format: search_date_format,
        onShow: function (ct) {
            this.setOptions({
                minDate: jQuery("#" + fromId).val() ? jQuery("#" + fromId).val() : false
            })
        },
        timepicker: false
    });
}

function initSorting() {
    jQuery('.orderingCol').click(function (e) {
        e.preventDefault();
        var ordering = jQuery(this).data('ordering');
        var direction = jQuery(this).data('direction');
        ajaxSearch(ordering, direction);
    });

    jQuery(".list-results #limit").removeAttr("onchange");
    jQuery(".list-results #limit").change(function (e) {
        e.preventDefault();
        ajaxSearch();
        return false;
    });
}

function initTags() {
    var $ = jQuery;
    if ($(".input_tags").length > 0) {
        var taglist = $(".input_tags").val();
        var tagArr = taglist.split(",");
        $('.chk_ftags').each(function () {
            var temp = $(this).val();
            if (tagArr.indexOf(temp) > -1) {
                $(this).prop('checked', true);
            }
        });
    }
    if ($("#filter_catid").length > 0) {
        catChange("filter_catid");
    }
}

function catChange(filterCat) {
    var $ = jQuery;
    var catId = $("#" + filterCat).val();
    if (catId == "") {
        $('.chk_ftags').parent().show();
        return;
    }
    if ($('.chk_ftags').length == 0) {
        return;
    }
    var catChilds = getChilds(catId, window.catDatas);
    var childTags = getChildTags(catChilds);
    if (childTags.length > 0) {

        $('.chk_ftags').each(function () {
            var chkVal = $(this).val();
            if (childTags.indexOf(chkVal) == -1) {
                $(this).prop('checked', false);
                $(this).parent().hide();
            } else {
                $(this).parent().show();
            }
        });
        fillInputTags();
    } else {
        $('.chk_ftags').prop('checked', false);
        $(".input_tags").val("");
    }
}

function fillInputTags() {
    var tagVal = [];
    jQuery('.chk_ftags').each(function () {
        if (this.checked && jQuery(this).is(":visible")) {
            tagVal.push(jQuery(this).val());
        }
    });
    if (tagVal.length > 0) {
        jQuery(".input_tags").val(tagVal.join(","));
    } else {
        jQuery(".input_tags").val("");
    }
}

function getChilds(catId, catDatas) {
    var lft = 0;
    var rgt = 0;
    for (i = 0; i < catDatas.length; i++) {
        if (catDatas[i].id == catId) {
            lft = catDatas[i].lft;
            rgt = catDatas[i].rgt;
        }
    }
    var result = [];
    result.push(catId);
    for (i = 0; i < catDatas.length; i++) {
        if (catDatas[i].lft > lft && catDatas[i].rgt < rgt) {
            result.push(catDatas[i].id);
        }
    }
    return result;
}

function getChildTags(catChilds) {
    var result = [];
    for (i = 0; i < catChilds.length; i++) {
        if (catChilds[i] in window.catTags) {
            jQuery.merge(result, window.catTags[catChilds[i]]);
        }
    }

    return result;
}

function ajaxSearch(ordering, direction) {
    var $ = jQuery;
    var sform = $("#adminForm");

    // get the form data
    var formData = {
        'q': $(sform).find('input[name=q]').val(),
        'catid': $(sform).find('select[name=catid]').val(),
        'cattype': $(sform).find('.dropfiles_cat_type').val(),
        'ftags': $(sform).find('input[name=ftags]').val(),
        'cfrom': $(sform).find('input[name=cfrom]').val(),
        'cto': $(sform).find('input[name=cto]').val(),
        'ufrom': $(sform).find('input[name=ufrom]').val(),
        'uto': $(sform).find('input[name=uto]').val(),
    };

    formData = cleanObj(formData);
    if (jQuery.isEmptyObject(formData)) {
        $("#txtfilename").focus();
        return false;
    }

    if (typeof ordering != 'undefined') formData.ordering = ordering;
    if (typeof direction != 'undefined') formData.dir = direction;
    //pagination
    if ($(sform).find('input[name=limitstart]').length > 0) {
        formData.limitstart = $(sform).find('input[name=limitstart]').val();
    }
    if ($(sform).find('select[name=limit]').length > 0) {
        formData.limit = $(sform).find('select[name=limit]').val();
    }

    if ($(sform).find('input[name=Itemid]').length > 0) {
        formData.Itemid = $(sform).find('input[name=Itemid]').val();
    }
    var filter_url = jQuery.param(formData);
    window.history.pushState(formData, "", basejUrl + "index.php?option=com_dropfiles&view=frontsearch&" + filter_url);

    formData.view = "frontsearch";
    formData.format = "raw";
    $.ajax({
        type: "POST",
        url: basejUrl + 'index.php?option=com_dropfiles',
        data: formData,
        beforeSend: function () {
            $("#dropfiles-results").prepend($("#loader").html());
        }
    }).done(function (result) {
        $("#dropfiles-results").html(result);
        initSorting();
        dropfilesColorboxInit();
    });
}

jQuery(document).ready(function ($) {
    initDatepicker();
    initSorting();
    initTags();

    $(".chk_ftags").click(function () {
        fillInputTags();
    });
    $("#filter_catid").change(function () {
        catChange("filter_catid");
        var filter_cat = $(this).find(':selected').val();
        var filter_cattype = $(this).find('option[value="' + filter_cat + '"]').data('type');
        $('.dropfiles_cat_type').val(filter_cattype);
    });
    $("#search_catid").change(function () {
        catChange("search_catid");
    });

    var oldJoomlaSubmit;
    if (typeof Joomla != 'undefined') {
        oldJoomlaSubmit = Joomla.submitform;
        Joomla.submitform = function ($task) {
            ajaxSearch();
            if ($task) {
                oldJoomlaSubmit($task);
            }
        };
    }
    $("#adminForm.dropfiles_search").submit(function (e) {
        e.preventDefault();

        return false;
    });
    jQuery('.icon-date').click(function () {
        var txt = jQuery(this).attr('data-id');
        jQuery('#' + txt).datetimepicker('show');
    });
    jQuery('.feature-toggle').toggle(
        function () {
            var container = jQuery(this).parents('.by-feature');
            jQuery(container).find('.feature').slideUp();
            jQuery(container).removeClass('feature-border');
            jQuery(this).removeClass('feature-toggle-up').addClass('feature-toggle-down');
        },
        function () {
            var container = jQuery(this).parents('.by-feature');
            jQuery(container).find('.feature').slideDown();
            jQuery(container).addClass('feature-border');
            jQuery(this).removeClass('feature-toggle-down').addClass('feature-toggle-up');
        }
    );

    //ajax filters
    $("#btnsearchbelow, #btnsearch").click(function (e) {
        e.preventDefault();
        ajaxSearch();
    });

    $("#btnReset").click(function (e) {
        e.preventDefault();
        resetFilters();
        $("#dropfiles-results").html("");
    });

    jQuery('.list-results table tr td a.file-item').click(function (e) {
        e.preventDefault();
        var popupid = "#popup-" + jQuery(this).attr('id');
        var dd = jQuery(popupid).dialog({
            minHeight: 250,
            minWidth: 250,
            modal: true,
            draggable: false,
            resizable: false
        });
        jQuery('.ui-widget-overlay').click(function () {
            dd.dialog('close');
        });
        return false;
    });

    resetFilters = function (formSelect) {

        var sform = $("#adminForm");
        if (typeof formSelect != 'undefined') {
            sform = $(formSelect);
        }

        var inputs = $(sform).find('input, select');
        $.each(inputs, function (i, el) {
            var eType = $(el).attr('type');
            if (eType == 'checkbox') {
                $(el).prop('checked', false);
            } else {
                $(el).val("").trigger('change').trigger("liszt:updated").trigger("chosen:updated");
                if ($(el).hasClass("tagit")) {
                    $(el).tagit("removeAll");
                }
            }

        })
    };

    populateFilters = function (filters) {

        var sform = $("#adminForm");
        $.each(filters, function (f, v) {
            var els = $(sform).find('input[name=' + f + '], select[name=' + f + ']');
            if (els.length > 0) {
                $(els).val(v).trigger('change').trigger("liszt:updated").trigger("chosen:updated");
                if ($(els).hasClass("tagit")) {
                    $(els).tagit("removeAll");
                    if (v != "") {
                        var tgs = v.split(",");
                        for (var i = 0; i < tgs.length; i++) {
                            $(els).tagit("createTag", tgs[i]);
                        }
                    }

                }
            }
        });
    };

    //Remove propery with empty value
    cleanObj = function (obj) {
        for (var k in obj) {
            if (obj.hasOwnProperty(k)) {
                if (!obj[k]) delete obj[k];
            }
        }
        return obj;
    };

    //back on browser
    window.addEventListener('load', function () {
        setTimeout(function () {
            jQuery(window).on('popstate', function (event) {
                var state = event.originalEvent.state;
                resetFilters();
                if (state != null) {
                    var formData = state;
                    populateFilters(formData);
                    formData.view = "frontsearch";
                    formData.format = "raw";
                    $.ajax({
                        type: "POST",
                        url: basejUrl + 'index.php?option=com_dropfiles',
                        data: formData,
                    }).done(function (result) {
                        $("#dropfiles-results").html(result);
                    });
                } else {
                    $("#dropfiles-results").html("");
                }
            });
        }, 100);
    }, false);

});
