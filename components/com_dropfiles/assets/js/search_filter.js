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
                $(this).parent(".tags-item").addClass('active');
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
    addtoSearchbuttonbelow();
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
        showViewOption();
    });
}

function openSearchfilter(evt, searchName) {
    evt.preventDefault();

    var $ = jQuery;
    var $this = $(evt.target);

    $this.parent().find('.tablinks').removeClass('active');
    $this.addClass('active');

    $this.parent().parent().find('.dropfiles_tabcontainer .dropfiles_tabcontent').removeClass('active');
    $this.parent().parent().find('.dropfiles_tabcontainer #' + searchName).addClass('active');

    return false;
}

function selectdChecktags() {
    var $ = jQuery;
    jQuery('li.tags-item').on('click', function () {
        $(this).toggleClass("active");
        if ($(this).hasClass("active")) {
            $(this).children("input[type='checkbox']").prop('checked', true);
        } else {
            $(this).children("input[type='checkbox']").prop('checked', false);
        }
        var tagVal = [];
        jQuery(".chk_ftags").each(function () {
            if ($(this).prop("checked") == true) {
                tagVal.push($(this).val());
            }
        });
        if (tagVal.length > 0) {
            jQuery(".input_tags").val(tagVal.join(","));
        } else {
            jQuery(".input_tags").val("");
        }
    });
}

function dropfilescancelSelectedCate() {
    var $, dropfileslCurrentselectedCate;
    $ = jQuery;
    dropfileslCurrentselectedCate = $(".categories-filtering .cate-lab");
    $(".cate-lab .cancel").unbind('click').on('click', function () {
        if (dropfileslCurrentselectedCate.hasClass('display-cate')) {
            dropfileslCurrentselectedCate.removeClass('display-cate');
        }
        dropfileslCurrentselectedCate.empty();
        dropfileslCurrentselectedCate.append("<label id='root-cate'>" + Joomla.JText._('COM_DROPFILES_SEARCH_FILES_CATEGORY_FILTERING', 'Files category') + "</label>");
        $(".categories-filtering #dropfiles-listCate #filter_catid").val('').trigger('change');
        if($(".categories-filtering .cate-item").hasClass("checked")) {
            $(".categories-filtering .cate-item").removeClass("checked");
        } else {
            if($(".categories-filtering .cate-item").hasClass("choosed")) {
                $(".categories-filtering .cate-item").removeClass("choosed");
            }
        }
        catChange("filter_catid");
    });
}

function dropfilesShowCateReload() {
    var $, SelectedCateReloadCase, dropfilesdisplayCateReloadCase;
    $ = jQuery;
    SelectedCateReloadCase = $(".categories-filtering .cate-item.choosed label").text();
    if ($(".mod_dropfiles_search .cate-item.choosed label").length) {
        SelectedCateReloadCase = $(".mod_dropfiles_search .cate-item.choosed label").text();
    }
    dropfilesdisplayCateReloadCase = $(".categories-filtering .cate-lab");
    var selectedCatecontentReloadCase = "<label>" + SelectedCateReloadCase + "</label>";
    if($(".cate-item.choosed").length > 0) {
        dropfilesdisplayCateReloadCase.addClass('display-cate');
        dropfilesdisplayCateReloadCase.empty();
        dropfilesdisplayCateReloadCase.append(selectedCatecontentReloadCase);
        if(dropfilesdisplayCateReloadCase.text() !== null ) {
            dropfilesdisplayCateReloadCase.append('<a class="cancel"></a>');
        }
    }
    dropfilescancelSelectedCate();
}

function parentFolderIcon() {
    var $ = jQuery;
    $("li.cate-item").each(function () {
        var count = $(this).find('span.child-cate').length;
        var prelevel = $(this).prev().attr("data-catlevel");
        var catelevel = $(this).attr("data-catlevel");
        if((count == 1 && catelevel > prelevel) || (count == 1 && prelevel == 9)) {
            $(this).prev().addClass("parent-cate");
        }
    });
}

function showCategory() {
    var $ = jQuery;
    $(".categories-filtering .cateicon").unbind('click').on('click', function (e) {
        e.preventDefault();
        var $this = $(this);
        var $container = $this.parent();
        $('#dropfiles-listCate', $container).toggle();
        $('li.cate-item', $container).unbind('click').on('click', function () {
            var selected_catid = $(this).data('catid').toString();
            $('li.cate-item.checked', $container).removeClass("checked");
            $('li.cate-item', $container).removeClass("choosed");

            $(this).addClass("checked");
            if ($("#filter_catid", $container).length) {
                $("#filter_catid option", $container).removeAttr('selected');
                $("#filter_catid option", $container).each(function () {
                    var related_catid = $(this).val();
                    if (related_catid === selected_catid) {
                        $(this).attr('selected', 'selected');
                    }
                });
                $("#filter_catid", $container).val(selected_catid).trigger("change");
            }
            if ($("#search_catid", $container).length) {
                $("#search_catid option", $container).removeAttr('selected');
                $("#search_catid option", $container).each(function () {
                    var related_catid = $(this).val();
                    if (related_catid === selected_catid) {
                        $(this).attr('selected', 'selected');
                    }
                });
                $("#search_catid", $container).val(selected_catid).trigger("change");
            }
            $('#dropfiles-listCate', $container).hide();

            // Show selected category
            var dropfilesSelectedCatename = $(".cate-item.checked label", $container).text();
            var dropfilesdisplayCate = $('.cate-lab', $container);
            if($('.showitems', $container).length > 0) {
                $('.show-selected-cate', $container).css("display", "");
            } else {
                var selectedCatecontent = "<label>" + dropfilesSelectedCatename + "</label>";
                $('.show-selected-cate', $container).css("display", "block");
                if($('.cate-item.checked', $container).length === 1) {
                    dropfilesdisplayCate.addClass('display-cate');
                    dropfilesdisplayCate.empty();
                    dropfilesdisplayCate.append(selectedCatecontent);
                    if(dropfilesdisplayCate.text() !== null ) {
                        dropfilesdisplayCate.append('<a class="cancel"></a>');
                    }
                }
            }

            dropfilescancelSelectedCate();

            catChange("filter_catid");
        });

        $(document).mouseup(function(e) {
            if (!$(".categories-filtering > .ui-widget").is(e.target) // if the target of the click isn't the container...
                && !$(".categories-filtering .cateicon").is(e.target)
                && $(".categories-filtering > .ui-widget").has(e.target).length === 0) // ... nor a descendant of the container
            {
                $(".categories-filtering > .ui-widget").hide();
            }
        });
    });
}

//add class to search button
function addtoSearchbuttonbelow() {
    var $ = jQuery;
    $("div#Tags").each(function () {
        if ($(this).find('.tags-filtering').length) {
            $(this).siblings('.box-btngroup-below').addClass("searchboxClass");
        }
    });
}

function dropfilesDateFilter() {
    var $ = jQuery;
    $('#Filter').each(function () {
        if ($(this).find('.dropfiles-date-hidden').length) {
            $(this).addClass('no-date');
        } else {
            $(this).removeClass('no-date');
        }
    });

}

function  noTagscase() {
    var $ =jQuery;
    if($("#Tags .no-tags").length > 0) {
        $(".feature .box-btngroup-below").addClass("notags-case");
    } else {
        $(".feature .box-btngroup-below").removeClass("notags-case");
    }
}

function  initdefaultOption() {
    var $ = jQuery;
    var checkitem = $('.mediaTableMenu .media-item');
    var showList = [];
    checkitem.each(function () {
        if ($(this).prop("checked") === true) {
            showList.push($(this).val());
        }
    });
    if (showList.length > 0) {
        jQuery("#total-media-list").val(showList.join(","));
    } else {
        jQuery("#total-media-list").val("");
        showList = new Array('version','size','hits','date_added','download');
    }
    var desc = "";
    var ver = "";
    var size = "";
    var hits = "";
    var dateadd = "";
    var download = "";
    for(var i = 0; i<showList.length;i++) {
        if(showList[i] == "description" ) {
            desc = "description";
        }
        if(showList[i] == "version") {
            ver = "version";
        }
        if(showList[i] == "size") {
            size = "size";
        }
        if(showList[i] == "hits") {
            hits = "hits";
        }
        if(showList[i] == "date_added") {
            dateadd = "date_added";
        }
        if(showList[i] == "download") {
            download = "download";
        }
    }
    if(desc === "description") {
        jQuery(".file_desc").removeClass('filehidden');
    } else {
        jQuery(".file_desc").addClass('filehidden');
    }
    if (ver === "version") {
        jQuery(".file_version").removeClass('filehidden');
    } else {
        jQuery(".file_version").addClass('filehidden');
    }
    if (size === "size") {
        jQuery(".file_size").removeClass('filehidden');
    } else {
        jQuery(".file_size").addClass('filehidden');
    }
    if (hits === "hits") {
        jQuery(".file_hits").removeClass('filehidden');
    } else {
        jQuery(".file_hits").addClass('filehidden');
    }
    if (dateadd === "date_added") {
        jQuery(".file_created").removeClass('filehidden');
    } else {
        jQuery(".file_created").addClass('filehidden');
    }
    if (download === "download") {
        jQuery(".file_download").removeClass('filehidden');
    } else {
        jQuery(".file_download").addClass('filehidden');
    }
}

function showViewOption() {
    var $ = jQuery;
    $('.mediaTableMenu').on('click', function () {
        $(this).addClass('showlist');
        $('.mediaTableMenu .media-item').on('click', function () {
            initdefaultOption();
            if($(".list-results .file_desc").hasClass("filehidden") && $(".list-results .file_created").hasClass("filehidden") ) {
                $(".list-results .file_download").addClass("file_download_inline");
            } else {
                $(".list-results .file_download").removeClass("file_download_inline");
            }
            var checkall = $(".list-results .table thead th");
            if(!checkall.hasClass("filehidden")) {
                $(".list-results .file_title").addClass("adv_file_tt");
            } else {
                $(".list-results .file_title").removeClass("adv_file_tt");
            }
        });

        $(document).mouseup(e => {
            if (!$(".mediaTableMenu").is(e.target) // if the target of the click isn't the container...
                && $(".mediaTableMenu").has(e.target).length === 0) // ... nor a descendant of the container
            {
                $(".mediaTableMenu").removeClass('showlist');
            }
        });
    });
}

function showtbResultonMobile() {
    if(jQuery("#dropfiles-results").width() <=420) {
        jQuery(".file_version").css("display", "none");
        jQuery(".file_size").css("display", "none");
        jQuery(".file_hits").css("display", "none");
        jQuery(".file_created").css("display", "none");
    }
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

    jQuery('.feature-toggle').click(function () {
        var container = jQuery(this).parents('.by-feature');
        jQuery(container).find('.feature').slideToggle('slow', function () {
            jQuery(".feature-toggle").toggleClass(function () {
                if (jQuery(this).is(".toggle-arrow-up-alt")) {
                    return "toggle-arrow-down-alt";
                } else {
                    return "toggle-arrow-up-alt";
                }
            });
        });
    });

    //ajax filters
    $("#btnsearchbelow, #btnsearch").click(function (e) {
        e.preventDefault();
        ajaxSearch();
    });

    $("#mod_btnReset").click(function (e) {
        e.preventDefault();
        resetFilters();
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

        });
        $('.tags-item').removeClass('active');
        if($(".cate-lab .cancel").length === 1) {
            $('.cate-item').removeClass('checked');
            var dropfileslCurrentselectedCate = $(".categories-filtering .cate-lab");
            if (dropfileslCurrentselectedCate.hasClass('display-cate')) {
                dropfileslCurrentselectedCate.removeClass('display-cate');
            }
            dropfileslCurrentselectedCate.empty();
            dropfileslCurrentselectedCate.append("<label id='root-cate'>" + Joomla.JText._('COM_DROPFILES_SEARCH_FILES_CATEGORY_FILTERING', 'Files category') + "</label>");
        }
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

    //get checktags
    selectdChecktags();

    //show cate
    showCategory();

    //display selected category when reload.
    dropfilesShowCateReload();

    //Search category
    $('#dropfilesCategorySearch').on('keydown keyup', function(e) {
        if (e.keyCode === 13 || e.which === 13 || e.key === 'Enter')
        {
            e.preventDefault();
            return;
        }
        var scateList, filter, labl, txtValue;
        var $this = $(this);
        scateList = $("li.cate-item", $this.parent().parent());

        filter =  $this.val().toUpperCase();
        scateList.each(function () {
            labl = $(this).find("label");
            txtValue = labl.text().toUpperCase();
            if (txtValue.indexOf(filter) > -1) {
                $(this).css("display","");
            } else {
                $(this).css("display", "none");
            }
        });
    });

    //Set icons
    parentFolderIcon();

    //get searchbox
    addtoSearchbuttonbelow();

    dropfilesDateFilter();

    noTagscase();

    initdefaultOption();

    showViewOption();

    showtbResultonMobile();
});
