/**
 * Dropfiles
 *
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 * @package Dropfiles
 * @copyright Copyright (C) 2013 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2013 Damien Barrère (http://www.crac-design.com). All rights reserved.
 * @license GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */

jQuery(document).ready(function ($) {
    var sourcefiles = $("#dropfiles-template-tree-files").html();
    var type_sourcefiles = $("#dropfiles-template-tree-type").html();
    var sourcecategories = $("#dropfiles-template-tree-categories").html();
    var sourcefile = $("#dropfiles-template-tree-box").html();

    sourcefiles = fixJoomlaSef(sourcefiles);
    sourcefile = fixJoomlaSef(sourcefile);

    var tree_hash = window.location.hash;
    var tree_load_done = false;
    var ggd_root_cat = $('.dropfiles-content-tree').data('category');

    initInputSelected();
    Handlebars.registerHelper('bytesToSize', function (bytes) {
        return bytesToSize(bytes);
    });

    function initInputSelected() {
        $(document).on('change', ".dropfiles-content-tree.dropfiles-content-multi input.cbox_file_download", function () {
            inputSelect($(this).parents('.dropfiles-content')[0]);
        });
    }
    function inputSelect(context) {
        var selectedFiles = $("input.cbox_file_download:checked", context);
        var filesId = [];
        if (selectedFiles.length) {
            selectedFiles.each(function (index, file) {
                filesId.push($(file).data('id'));
            });
        }
        if (filesId.length > 0) {
            $(".dropfilesSelectedFiles", context).remove();
            $('<input type="hidden" class="dropfilesSelectedFiles" value="' + filesId.join(',') + '" />')
                .insertBefore($(" .tree-list", context));
            hideDownloadAllBtn(context, true);
            $(".tree-download-selected", context).remove();
            var downloadSelectedBtn = $('<a href="javascript:void(0);" class="tree-download-selected download-selected" style="display: block;">' + Joomla.JText._('COM_DROPFILES_DOWNLOAD_SELECTED', 'Download selected') + '<i class="zmdi zmdi-check-all dropfiles-download-category"></i></a>');
            downloadSelectedBtn.appendTo($(".categories-head", context));
            initDownloadSelected();
        } else {
            $(".dropfilesSelectedFiles", context).remove();
            $(".tree-download-selected", context).remove();
            hideDownloadAllBtn(context);
        }
    }
    function hideDownloadAllBtn(context, hide) {
        var downloadCatButton = $(".tree-download-category", context);
        var selectFileInputs = $("input.cbox_file_download", context);

        if (downloadCatButton.length === 0) {
            if (selectFileInputs.length > 0) {
                if ($(".categories-head", context).length) {
                    var downloadAllBtn = $('<a href="javascript:void(0);" class="tree-download-category download-all" style="display: block;">' + Joomla.JText._('COM_DROPFILES_DOWNLOAD_ALL', 'Download all') + '<i class="zmdi zmdi-check-all"></i></a>');
                    downloadAllBtn.prependTo($(".categories-head", context));
                } else {
                    var downloadAllBtn = $('<a href="javascript:void(0);" class="tree-download-category download-all" style="display: block;">' + Joomla.JText._('COM_DROPFILES_DOWNLOAD_ALL', 'Download all') + '<i class="zmdi zmdi-check-all"></i></a>');
                    downloadAllBtn.insertAfter( $('#current_category_slug', context));
                }
            } else {
                return;
            }
        } else {
            if (selectFileInputs.length === 0) {
                downloadCatButton.remove();
                return;
            }
        }

        if (hide) {
            $(".tree-download-category", context).hide();
        } else {
            $(".tree-download-category", context).show();
        }
    }

    function initDownloadSelected() {
        $('.dropfiles-content-tree.dropfiles-content-multi .tree-download-selected').on('click', function () {
            var context = $(this).parents('.dropfiles-content')[0];
            if ($('.dropfilesSelectedFiles', context).length > 0) {
                var category_name = $('#current_category_slug', context).val();
                var selectedFilesId = $('.dropfilesSelectedFiles', context).val();
                $.ajax({
                    url: dropfilesBaseUrl + "index.php?option=com_dropfiles&task=frontfile.zipSeletedFiles&filesId=" + selectedFilesId + "&dropfiles_category_id=" + $(context).attr('data-category'),
                    dataType: "json"
                }).done(function (results) {
                    if (results.status === 'success') {
                        var hash = results.hash;
                        window.location.href = dropfilesBaseUrl + "index.php?option=com_dropfiles&task=frontfile.downloadZipedFile&hash=" + hash + "&dropfiles_category_id=" + $(context).attr('data-category') + "&dropfiles_category_name=" + category_name;
                    } else {
                        alert(results.message);
                    }
                })
            }
        });
    }

    initClickFile();

    tree_hash = tree_hash.replace('#', '');
    if (tree_hash != '') {
        var hasha = tree_hash.split('-');
        var hash_category_id = hasha[0];
        if (!parseInt(hash_category_id)) {
            return;
        }
        setTimeout(function () {
            tree_loadcategory(hash_category_id, $('.dropfiles-content-tree.dropfiles-content-multi').data('category'));
        }, 100)
    }
    initManageFile($('.dropfiles-content-tree.dropfiles-content-multi a.catlink').parents('.dropfiles-content-tree.dropfiles-content-multi').data('category'));

    $('.dropfiles-content-tree.dropfiles-content-multi a.catlink').unbind('click.cat').bind('click.cat', function (e) {
        e.preventDefault();
        if (typeof $(this).data('clicked') !== 'undefined') {
            // Previously clicked, stop actions
            e.preventDefault();
            e.stopPropagation();
        } else {
            // Mark to ignore next click
            $(this).data('clicked', true);

            load($(this).parents('.dropfiles-content-tree.dropfiles-content-multi').data('category'), $(this).data('idcat'), $(this));
            $(this).parent().removeClass('collapsed').addClass('expanded');
        }
    });

    function tree_loadcategory($catid, $sourcecat) {
        $.ajax({
            url: dropfilesBaseUrl + "index.php?option=com_dropfiles&task=categories.getParentsCats&id=" + $catid + "&displaycatid=" + $sourcecat,
            dataType: "json"
        }).done(function (ob) {
            load($sourcecat, ob[0], $('.dropfiles-content-tree [data-idcat="' + ob[0] + '"]'), ob);
        });
    }

    function initClickFile() {
        $('.dropfiles-content-tree.dropfiles-content .dropfile-file-link').unbind('click').click(function (e) {
            var href = $(this).attr('href');
            if (href !== '#') {
                return;
            }
            e.preventDefault();
            fileid = $(this).data('id');
            catid = $(this).closest('.directory.selected').find("a.catlink").data('idcat');
            if (!catid) {
                catid = $(this).parents(".dropfiles-content-tree").data('current');
            }
            $.ajax({
                url: dropfilesBaseUrl + "index.php?option=com_dropfiles&view=frontfile&format=json&id=" + fileid + "&catid=" + catid,
                dataType: "json",
                beforeSend: function() {
                    // setting a timeout
                    if($('body').has('dropfiles-tree-box-loader') !== true) {
                        $('body').append('<div class="dropfiles-tree-box-loader"></div>');
                    }
                }
            }).done(function (file) {
                var template = Handlebars.compile(sourcefile);
                var html = template(file);
                box = $("#dropfiles-box-tree");
                $('.dropfiles-tree-box-loader').each(function () {
                    $(this).remove();
                });
                if (box.length === 0) {
                    $('body').append('<div id="dropfiles-box-tree" style="display: none;"></div>');
                    box = $("#dropfiles-box-tree");
                }
                box.empty();
                box.prepend(html);
                box.click(function (e) {
                    if ($(e.target).is('#dropfiles-box-tree')) {
                        box.hide();
                    }
                    $('#dropfiles-box-tree').unbind('click.box-tree').bind('click.box-tree', function (e) {
                        if ($(e.target).is('#dropfiles-box-tree')) {
                            box.hide();
                        }
                    });
                });
                $('#dropfiles-box-tree .dropfiles-close').click(function (e) {
                    e.preventDefault();
                    box.hide();
                });

                box.show();
                if (typeof(dropfilesColorboxInit) !== 'undefined') {
                    dropfilesColorboxInit();
                }

                dropblock = box.find('.dropblock');

                if ($(window).width() < 400) {
                    dropblock.css('margin-top', '0');
                    dropblock.css('margin-left', '0');
                    dropblock.css('top', '0');
                    dropblock.css('left', '0');
                    dropblock.height($(window).height() - parseInt(dropblock.css('padding-top'), 10) - parseInt(dropblock.css('padding-bottom'), 10));
                    dropblock.width($(window).width() - parseInt(dropblock.css('padding-left'), 10) - parseInt(dropblock.css('padding-right'), 10));
                } else {
                    dropblock.css('margin-top', (-(dropblock.height() / 2) - 20) + 'px');
                    dropblock.css('margin-left', (-(dropblock.width() / 2) - 20) + 'px');
                    dropblock.css('height', '');
                    dropblock.css('width', '');
                    dropblock.css('top', '');
                    dropblock.css('left', '');
                }
            });
        });
    }

    function wantDelete(item, arr) {
        for (var i = 0; i < arr.length; i++) {
            if (arr[i] == item) {
                arr.splice(i, 1);
                break;
            }
        }
    }

    function load(sourcecat, category, elem, loadcats) {
        if (typeof(category) == 'undefined') {
            return;
        }
        if (!jQuery.isEmptyObject(loadcats)) {
            wantDelete(category, loadcats);
        }
        var pathname = window.location.pathname;
        $('.dropfiles-content-tree').find('.selected').removeClass('selected');
        elem.parent().addClass('selected');
        ul = elem.parent().children('ul');
        if (ul.length > 0) {
            //close cat
            ul.slideUp(500, null, function () {
                $(this).remove();
                elem.parent().removeClass('open expanded').addClass('collapsed');
                elem.parent().removeClass('dropfiles-loading-tree');
                elem.parent().find('.dropfiles-loading-tree-bg').remove();
            });
            elem.removeData('clicked');
            return;
        } else {
            elem.parent().addClass('dropfiles-loading-tree');
            elem.parent().prepend($('#dropfiles-loading-tree-wrap').html());
        }

        //Get categories
        $.ajax({
            url: dropfilesBaseUrl + "index.php?option=com_dropfiles&view=frontcategories&format=json&id=" + category,
            dataType: "json"
        }).done(function (categories) {
            window.history.pushState('', document.title, pathname + '#' + category + '-' + categories.category.alias);
            var template = Handlebars.compile(sourcecategories);
            var html = template(categories);
            if (categories.categories.length > 0) {
                elem.parents('li').append('<ul style="display:none;">' + html + '</ul>');
                $(".dropfiles-content-tree.dropfiles-content-multi[data-category=" + sourcecat + "] a.catlink").unbind('click.cat').bind('click.cat', function (e) {
                    e.preventDefault();
                    load($(this).parents('.dropfiles-content-tree.dropfiles-content-multi').data('category'), $(this).data('idcat'), $(this));
                    initClickFile();
                    initManageFile(category);
                });
            }

            //Get files
            $.ajax({
                url: dropfilesBaseUrl + "index.php?option=com_dropfiles&view=frontfiles&format=json&id=" + category,
                dataType: "json"
            }).done(function (content) {
                var template = Handlebars.compile(sourcefiles);
                var html = template(content);
                var type_template = Handlebars.compile(type_sourcefiles);
                var type_html = type_template(content);
                if (elem.parent().children('ul').length == 0) {
                    elem.parent().append(type_html);
                    elem.parent().append('<ul style="display:none;">' + html + '</ul>');
                } else {
                    elem.parent().children('ul').append(html);
                }

                initClickFile();
                initManageFile(category);
                elem.parent().children('ul').slideDown(500, null, function () {
                    elem.parent().addClass('open expanded');
                    elem.parent().removeClass('dropfiles-loading-tree collapsed');
                    elem.parent().find('.dropfiles-loading-tree-bg').remove();
                });
                if (!jQuery.isEmptyObject(loadcats)) {
                    var ccat = loadcats[0];
                    if (ccat != 'undefined') {
                        load(sourcecat, ccat, $('.dropfiles-content-tree [data-idcat="' + ccat + '"]'), loadcats);
                    }
                }
                if($('.tree-list.tree-hide-title').length) {
                    $('.tree-list.tree-hide-title li.directory ul li.ext .dropfile-file-link').hide();
                }
                if ($(".dropfiles-content-tree.dropfiles-content-multi[data-category=" + sourcecat + "] #current-category-link").length) {
                    $.ajax({
                        url: dropfilesBaseUrl + "index.php?option=com_dropfiles&task=category.isCloudCategory&id_category=" + category,
                        dataType: "json"
                    }).done(function (result) {
                        if (result.status === 'true') {
                            hideDownloadAllBtn($(elem).parents('.dropfiles-content')[0], true);
                        } else {
                            hideDownloadAllBtn($(elem).parents('.dropfiles-content')[0], false);
                        }
                    });
                    var current_download_link = $(".dropfiles-content-tree.dropfiles-content-multi[data-category=" + sourcecat + "] #current-category-link").val().toLowerCase();
                    if ($(".dropfiles-content-tree.dropfiles-content-multi[data-category=" + sourcecat + "] .tree-download-category").length) {
                        var root_download_link = $(".dropfiles-content-tree.dropfiles-content-multi[data-category=" + sourcecat + "] .tree-download-category").attr('href').toLowerCase();
                        if (current_download_link !== root_download_link) {
                            $(".dropfiles-content-tree.dropfiles-content-multi[data-category=" + sourcecat + "] .tree-download-category").attr('href', current_download_link);
                        }
                    }
                }
            });
            elem.removeData('clicked');
        });

    }

    function initManageFile(sourcecat) {
        if (typeof sourcecat == 'undefined') {
            sourcecat = $('.dropfiles-content-tree.dropfiles-content-multi').data('category');
        }
        var link_manager = $(".dropfiles-content-tree.dropfiles-content-multi").find('.openlink-manage-files').data('urlmanage');
        link_manager = link_manager + '&task=site_manage&site_catid=' + sourcecat + '&tmpl=dropfilesfrontend';
        $(".dropfiles-content-tree.dropfiles-content-multi").find('.openlink-manage-files').attr('href', link_manager);
    }

    // Remove the root url in case it's added by Joomla Sef plugin
    function fixJoomlaSef(template) {
        if (typeof template != 'undefined' && template != null) {
            var reg = new RegExp(dropfilesRootUrl + "{{", 'g');
            template = template.replace(reg, "{{");
        }

        return template;
    }

});