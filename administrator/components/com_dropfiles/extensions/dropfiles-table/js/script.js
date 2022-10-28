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


    var table_cParents = {};
    var table_tree = $('.dropfiles-foldertree-table');
    var table_hash = window.location.hash;
    var table_root_cat = $('.dropfiles-content-table').data('category');

    $(".dropfiles-content-table").each(function (index) {
        var table_topCat = $(this).data('category');
        table_cParents[table_topCat] = {parent_id: 0, id: table_topCat, title: $(this).find("h2").text()};
        $(this).find(".dropfilescategory.catlink").each(function (index) {
            var tempidCat = $(this).data('idcat');
            table_cParents[tempidCat] = {parent_id: table_topCat, id: tempidCat, title: $(this).text()};
        })
        initInputSelected(table_topCat);
        initDownloadSelected(table_topCat);
    })

    //load media tables
    $('.dropfiles-content-table.dropfiles-content .mediaTable').mediaTable();

    Handlebars.registerHelper('bytesToSize', function (bytes) {
        return bytesToSize(bytes);
    });

    function initInputSelected(sc) {
        $(document).on('change', ".dropfiles-content-table.dropfiles-content-multi[data-category=" + sc + "] input.cbox_file_download", function () {
            var rootCat = ".dropfiles-content-table.dropfiles-content-multi[data-category=" + sc + "]";
            var selectedFiles = $(rootCat + " input.cbox_file_download:checked");
            var filesId = [];
            if (selectedFiles.length) {
                selectedFiles.each(function (index, file) {
                    filesId.push($(file).data('id'));
                });
            }
            if (filesId.length > 0) {
                $(rootCat + " .dropfilesSelectedFiles").remove();
                $('<input type="hidden" class="dropfilesSelectedFiles" value="' + filesId.join(',') + '" />')
                    .insertAfter($(rootCat).find(" #current_category_slug"));
                hideDownloadAllBtn(sc, true);
                $(rootCat + " .table-download-selected").remove();
                if ($(rootCat).find('.breadcrumbs').length) {
                    var downloadSelectedBtn = $('<a href="javascript:void(0);" class="table-download-selected download-selected" style="display: block;">' + Joomla.JText._('COM_DROPFILES_DOWNLOAD_SELECTED', 'Download selected') + '<i class="zmdi zmdi-check-all dropfiles-download-category"></i></a>');
                    downloadSelectedBtn.prependTo($(rootCat).find(".breadcrumbs.dropfiles-breadcrumbs-table"));
                } else {
                    var downloadSelectedBtn = $('<a href="javascript:void(0);" class="table-download-selected download-selected" style="display: block;">' + Joomla.JText._('COM_DROPFILES_DOWNLOAD_SELECTED', 'Download selected') + '<i class="zmdi zmdi-check-all dropfiles-download-category"></i></a>');
                    downloadSelectedBtn.insertAfter($(rootCat).find(" #current_category_slug"));
                }
            } else {
                $(rootCat + " .dropfilesSelectedFiles").remove();
                $(rootCat + " .table-download-selected").remove();
                hideDownloadAllBtn(sc, false);
            }
        });
    }

    function hideDownloadAllBtn(sc, hide) {
        var rootCat = ".dropfiles-content-table.dropfiles-content-multi[data-category=" + sc + "]";
        var downloadCatButton = $(rootCat + " .table-download-category");
        var selectFileInputs = $(rootCat + " input.cbox_file_download");

        if (downloadCatButton.length === 0) {
            if (selectFileInputs.length > 0) {
                if ($(rootCat).find('.breadcrumbs').length) {
                    var downloadAllBtn = $('<a href="javascript:void(0);" class="table-download-category download-all" style="display: block;">' + Joomla.JText._('COM_DROPFILES_DOWNLOAD_ALL', 'Download all') + '<i class="zmdi zmdi-check-all"></i></a>');
                    downloadAllBtn.prependTo($(rootCat).find(".breadcrumbs.dropfiles-breadcrumbs-table"));
                } else {
                    var downloadAllBtn = $('<a href="javascript:void(0);" class="table-download-category download-all" style="display: block;">' + Joomla.JText._('COM_DROPFILES_DOWNLOAD_ALL', 'Download all') + '<i class="zmdi zmdi-check-all"></i></a>');
                    downloadAllBtn.insertAfter($(rootCat).find(" #current_category_slug"));
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
            $(rootCat + " .table-download-category").hide();
        } else {
            $(rootCat + " .table-download-category").show();
        }
    }

    function initDownloadSelected(sc) {
        var rootCat = ".dropfiles-content-table.dropfiles-content-multi[data-category=" + sc + "]";
        $(document).on('click', rootCat + ' .table-download-selected', function () {
            if ($(rootCat).find('.dropfilesSelectedFiles').length > 0) {
                var current_category = $(rootCat).find('#current_category').val();
                var category_name = $(rootCat).find('#current_category_slug').val();
                var selectedFilesId = $(rootCat).find('.dropfilesSelectedFiles').val();
                $.ajax({
                    url: dropfilesBaseUrl + "index.php?option=com_dropfiles&task=frontfile.zipSeletedFiles&filesId=" + selectedFilesId + "&dropfiles_category_id=" + current_category,
                    dataType: "json",
                }).done(function (results) {
                    if (results.status === 'success') {
                        var hash = results.hash;
                        window.location.href = dropfilesBaseUrl + "index.php?option=com_dropfiles&task=frontfile.downloadZipedFile&hash=" + hash + "&dropfiles_category_id=" + current_category + "&dropfiles_category_name=" + category_name;
                    } else {
                        alert(results.message);
                    }
                })
            }
        });
    }

    function initClick() {
        $('.dropfiles-content-table.dropfiles-content-multi .catlink').click(function (e) {
            e.preventDefault();
            console.log('clicked');
            table_load($(this).parents('.dropfiles-content-table.dropfiles-content-multi').data('category'), $(this).data('idcat'), null);
        });
    }

    initClick();

    table_hash = table_hash.replace('#', '');
    if (table_hash != '') {
        var hasha = table_hash.split('-');
        var re = new RegExp("^(p[0-9]+)$");
        var page = null;
        var stringpage = hasha.pop();

        if (re.test(stringpage)) {
            page = stringpage.replace('p', '');
        }
        var hash_category_id = hasha[1];
        var hash_sourcecat = hasha[0];

        if (parseInt(hash_category_id) > 0 || hash_category_id === 'all_0') {
            if (hash_category_id == 'all_0') {
                hash_category_id = 0;
            }

            setTimeout(function () {
                table_load(hash_sourcecat, hash_category_id, page);
            }, 100);

        }
    }

    initManageFile($('.dropfiles-content-table.dropfiles-content-multi .catlink').parents('.dropfiles-content-default.dropfiles-content-multi').data('category'));

    function table_load(sourcecat, category, page) {
        console.log('table_load');
        var pathname = window.location.pathname;
        var container = $(".dropfiles-content-table.dropfiles-content-multi[data-category=" + sourcecat + "]");
        if (container.length == 0) {
            return;
        }
        $(document).trigger('dropfiles:category-loading');
        $(".dropfiles-content-table.dropfiles-content-multi[data-category=" + sourcecat + "]").find('#current_category').val(category);
        $(".dropfiles-content-table.dropfiles-content-multi[data-category=" + sourcecat + "] table tbody").empty();
        $(".dropfiles-content-table.dropfiles-content-multi[data-category=" + sourcecat + "] .dropfiles-categories").empty();
        $(".dropfiles-content-table.dropfiles-content-multi[data-category=" + sourcecat + "] .dropfiles-categories").html($('#dropfiles-loading-wrap').html());

        //Get categories
        $.ajax({
            url: dropfilesBaseUrl + "index.php?option=com_dropfiles&view=frontcategories&format=json&id=" + category + "&top=" + sourcecat,
            dataType: "json"
        }).done(function (categories) {
            console.log(sourcecat,category);
            if (page != null) {
                window.history.pushState('', document.title, pathname + '#' + sourcecat + '-' + category + '-' + categories.category.alias + '-p' + page);
            } else {
                console.log('pushState');
                window.history.pushState('', document.title, pathname + '#' + sourcecat + '-' + category + '-' + categories.category.alias);
            }

            $(".dropfiles-content-table.dropfiles-content-multi[data-category=" + sourcecat + "]").find('#current_category_slug').val(categories.category.alias);

            var tpltable_sourcecategories = container.parents().find("#dropfiles-template-table-categories-"+sourcecat ).html();
            if (tpltable_sourcecategories) {
                var template = Handlebars.compile(tpltable_sourcecategories);
                var html = template(categories);

                dropfiles_remove_loading($(".dropfiles-content-table.dropfiles-content-multi[data-category=" + sourcecat + "] .dropfiles-categories"));
                $(".dropfiles-content-table.dropfiles-content-multi[data-category=" + sourcecat + "] .dropfiles-categories").prepend(html);
            }

            if (table_tree.length) {
                var currentTree = container.find('.dropfiles-foldertree-table');
                currentTree.find('li').removeClass('selected');
                currentTree.find('i.zmdi').removeClass('zmdi-folder').addClass("zmdi-folder");

                currentTree.jaofoldertree('open', category, currentTree);

                var el = currentTree.find('a[data-file="' + category + '"]').parent();
                el.find(' > i.zmdi').removeClass("zmdi-folder").addClass("zmdi-folder");

                if (!el.hasClass('selected')) {
                    el.addClass('selected');
                }

            }

            //Get files
            $.ajax({
                url: dropfilesBaseUrl + "index.php?option=com_dropfiles&view=frontfiles&format=json&id=" + category,
                dataType: "json"
            }).done(function (content) {
                $.extend(content, categories);

                var tpltable_source = container.parents().find("#dropfiles-template-table-" + sourcecat).html();
                tpltable_source = fixJoomlaSef(tpltable_source);
                var template = Handlebars.compile(tpltable_source);
                var html = template(content);

                var tpltable_typesource = container.parents().find("#dropfiles-current-category-" + sourcecat).html();
                var type_template = Handlebars.compile(tpltable_typesource);
                var type_html = type_template(content);
                if ($(".dropfiles-content-table.dropfiles-content-multi[data-category=" + sourcecat + "] #current-category-type").length) {
                    $(".dropfiles-content-table.dropfiles-content-multi[data-category=" + sourcecat + "] #current-category-type").remove();
                }
                $(type_html).insertBefore($(" .dropfiles-container", $(".dropfiles-content-table.dropfiles-content-multi[data-category=" + sourcecat + "]")));
                $(".dropfiles-content-table.dropfiles-content-multi[data-category=" + sourcecat + "] table tbody").append(html);
                $(".dropfiles-content-table.dropfiles-content-multi[data-category=" + sourcecat + "] table tbody").trigger('change');
                $(".dropfiles-content-table.dropfiles-content-multi[data-category=" + sourcecat + "] .mediaTableMenu").find('input').trigger('change');

                for (i = 0; i < categories.categories.length; i++) {
                    table_cParents[categories.categories[i].id] = categories.categories[i];
                }

                table_breadcrum(sourcecat, category);

                initClick();
                initManageFile(sourcecat);
                if (typeof(dropfilesColorboxInit) !== 'undefined') {
                    dropfilesColorboxInit();
                }
                $(".dropfiles-content-table.dropfiles-content-multi[data-category=" + sourcecat + "] .dropfilesSelectedFiles").remove();
                $(".dropfiles-content-table.dropfiles-content-multi[data-category=" + sourcecat + "] .table-download-selected").remove();
                $.ajax({
                    url: dropfilesBaseUrl + "index.php?option=com_dropfiles&task=category.isCloudCategory&id_category=" + category,
                    dataType: "json"
                }).done(function (result) {
                    if (result.status === 'true') {
                        hideDownloadAllBtn(sourcecat, true);
                    } else {
                        hideDownloadAllBtn(sourcecat, false);
                    }
                });
                if ($(".dropfiles-content-table.dropfiles-content-multi[data-category=" + sourcecat + "] #current-category-link").length) {
                    var current_download_link = $(".dropfiles-content-table.dropfiles-content-multi[data-category=" + sourcecat + "] #current-category-link").val().toLowerCase();
                    if ($(".dropfiles-content-table.dropfiles-content-multi[data-category=" + sourcecat + "] .table-download-category").length) {
                        var root_download_link = $(".dropfiles-content-table.dropfiles-content-multi[data-category=" + sourcecat + "] .table-download-category").attr('href').toLowerCase();
                        if (current_download_link !== root_download_link) {
                            $(".dropfiles-content-table.dropfiles-content-multi[data-category=" + sourcecat + "] .table-download-category").attr('href', current_download_link);
                        }
                    }
                }
            });
        });
        $(document).trigger('dropfiles:category-loaded');
    }

    function table_breadcrum(sourcecat, catid) {

        links = [];
        current_Cat = table_cParents[catid];
        if (typeof (current_Cat) == 'undefined') {
            // todo: made breadcrumb working when reload page with hash
            return;
        }
        links.unshift(current_Cat);

        if (current_Cat.parent_id != 0) {
            while (table_cParents[current_Cat.parent_id]) {
                current_Cat = table_cParents[current_Cat.parent_id];
                links.unshift(current_Cat);
            }
        }

        html = '';
        for (i = 0; i < links.length; i++) {
            if (i < links.length - 1) {
                html += '<li><a class="catlink" data-idcat="' + links[i].id + '" href="javascript:void(0)">' + links[i].title + '</a><span class="divider"> &gt; </span></li>';
            } else {
                html += '<li><span>' + links[i].title + '</span></li>';
            }
        }
        $(".dropfiles-content-table[data-category=" + sourcecat + "] .dropfiles-breadcrumbs-table li").remove();
        $(".dropfiles-content-table[data-category=" + sourcecat + "] .dropfiles-breadcrumbs-table").append(html);

    }

    if (table_tree.length) {
        table_tree.each(function (index) {
            var table_topCat = $(this).parents('.dropfiles-content-table.dropfiles-content-multi').data('category');
            var rootCatName = $(this).parents('.dropfiles-content-table.dropfiles-content-multi').data('category-name');
            $(this).jaofoldertree({
                script: dropfilesBaseUrl + 'index.php?option=com_dropfiles&task=frontfile.getSubs&tmpl=component',
                usecheckboxes: false,
                root: table_topCat,
                showroot: rootCatName,
                onclick: function (elem, file) {
                    table_topCat = $(elem).parents('.dropfiles-content-table.dropfiles-content-multi').data('category');
                    if (table_topCat != file) {

                        $('.directory', $(elem).parents('.dropfiles-content-table.dropfiles-content-multi')).each(function() {
                            if (!$(this).hasClass('selected') && $(this).find('> ul > li').length === 0) {
                                $(this).removeClass('expanded');
                                $(this).addClass('collapsed');
                            }
                        });

                        $(elem).parents('.directory').each(function () {
                            var $this = $(this);
                            var category = $this.find(' > a');
                            var parent = $this.find('.icon-open-close');
                            if (parent.length > 0) {
                                if (typeof table_cParents[category.data('file')] == 'undefined') {
                                    table_cParents[category.data('file')] = {
                                        parent_id: parent.data('parent_id'),
                                        id: category.data('file'),
                                        title: category.text()
                                    };
                                }
                            }
                        });

                    }

                    table_load(table_topCat, file, null);
                }
            });
        });
    }

    function initManageFile(sourcecat) {
        if (typeof sourcecat == 'undefined') {
            sourcecat = $('.dropfiles-content-table.dropfiles-content-multi').data('category');
        }
        var current_category = $(".dropfiles-content-table.dropfiles-content-multi[data-category=" + sourcecat + "]").find('#current_category').val();
        var link_manager = $(".dropfiles-content-table.dropfiles-content-multi[data-category=" + sourcecat + "]").find('.openlink-manage-files').data('urlmanage');
        link_manager = link_manager + '&task=site_manage&site_catid=' + current_category + '&tmpl=dropfilesfrontend';
        $(".dropfiles-content-table.dropfiles-content-multi[data-category=" + sourcecat + "]").find('.openlink-manage-files').attr('href', link_manager);
        if ($(".backcategory").length) {
            $(".mediaTableWrapper.mediaTableWrapperWithMenu").addClass('mediaTableWrapper-chil');
        } else {
            $(".mediaTableWrapper.mediaTableWrapperWithMenu").removeClass('mediaTableWrapper-chil');
        }
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
