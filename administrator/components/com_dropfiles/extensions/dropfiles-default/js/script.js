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

    var default_hash = window.location.hash;
    var tree = $('.dropfiles-foldertree-default');
    var tree_source_cat = $('.dropfiles-content-default.dropfiles-content-multi').data('category');
    var cParents = {};

    $(".dropfiles-content-default").each(function (index) {
        var topCat = $(this).data('category');
        cParents[topCat] = {parent_id: 0, id: topCat, title: $(this).find("h2").text()};
        $(this).find(".dropfilescategory.catlink").each(function (index) {
            var tempidCat = $(this).data('idcat');
            cParents[tempidCat] = {parent_id: topCat, id: tempidCat, title: $(this).text()};
        })
        initInputSelected(topCat);
        initDownloadSelected(topCat);
    });

    Handlebars.registerHelper('bytesToSize', function (bytes) {
        return bytesToSize(bytes);
    });

    Handlebars.registerHelper('isGGExt', function (ext, options) {
        if (dropfilesGVExt.indexOf(ext) >= 0) {
            return options.fn(this);
        }
    });

    Handlebars.registerHelper('encodeURI', function (uri) {
        res = encodeURIComponent(uri);
        return res;
    });

    function default_initClick() {
        $('.dropfiles-content-default.dropfiles-content-multi .catlink').click(function (e) {
            e.preventDefault();
            default_load($(this).parents('.dropfiles-content-default.dropfiles-content-multi').data('category'), $(this).data('idcat'), null);
        });
    }

    function initInputSelected(sc) {
        $(document).on('change', ".dropfiles-content-default.dropfiles-content-multi[data-category=" + sc + "] input.cbox_file_download", function () {
            var rootCat = ".dropfiles-content-default.dropfiles-content-multi[data-category=" + sc + "]";
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
                $(rootCat + " .default-download-selected").remove();
                if ($(rootCat).find('.breadcrumbs').length) {
                    var downloadSelectedBtn = $('<a href="javascript:void(0);" class="default-download-selected download-selected" style="display: block;">' + Joomla.JText._('COM_DROPFILES_DOWNLOAD_SELECTED', 'Download selected') + '<i class="zmdi zmdi-check-all dropfiles-download-category"></i></a>');
                    downloadSelectedBtn.prependTo($(rootCat).find(".breadcrumbs.dropfiles-breadcrumbs-default"));
                } else {
                    var downloadSelectedBtn = $('<a href="javascript:void(0);" class="default-download-selected download-selected" style="display: block;">' + Joomla.JText._('COM_DROPFILES_DOWNLOAD_SELECTED', 'Download selected') + '<i class="zmdi zmdi-check-all dropfiles-download-category"></i></a>');
                    downloadSelectedBtn.insertAfter($(rootCat).find(" #current_category_slug"));
                }
            } else {
                $(rootCat + " .dropfilesSelectedFiles").remove();
                $(rootCat + " .default-download-selected").remove();
                hideDownloadAllBtn(sc, false);
            }
        });
    }

    function hideDownloadAllBtn(sc, hide) {
        var rootCat = ".dropfiles-content-default.dropfiles-content-multi[data-category=" + sc + "]";
        var downloadCatButton = $(rootCat + " .default-download-category");
        var selectFileInputs = $(rootCat + " input.cbox_file_download");

        if (downloadCatButton.length === 0) {
            if (selectFileInputs.length > 0) {
                if ($(rootCat).find('.breadcrumbs').length) {
                    var downloadAllBtn = $('<a href="javascript:void(0);" class="default-download-category download-all" style="display: block;">' + Joomla.JText._('COM_DROPFILES_DOWNLOAD_ALL', 'Download all') + '<i class="zmdi zmdi-check-all"></i></a>');
                    downloadAllBtn.prependTo($(rootCat).find(".breadcrumbs.dropfiles-breadcrumbs-default"));
                } else {
                    var downloadAllBtn = $('<a href="javascript:void(0);" class="default-download-category download-all" style="display: block;">' + Joomla.JText._('COM_DROPFILES_DOWNLOAD_ALL', 'Download all') + '<i class="zmdi zmdi-check-all"></i></a>');
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
            $(rootCat + " .default-download-category").hide();
        } else {
            $(rootCat + " .default-download-category").show();
        }
    }

    function initDownloadSelected(sc) {
        var rootCat = ".dropfiles-content-default.dropfiles-content-multi[data-category=" + sc + "]";
        $(document).on('click', rootCat + ' .default-download-selected', function () {
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

    default_initClick();
    initManageFile($('.dropfiles-content-default.dropfiles-content-multi .catlink').parents('.dropfiles-content-default.dropfiles-content-multi').data('category'));

    default_hash = default_hash.replace('#', '');

    if (default_hash !== '') {
        var hasha = default_hash.split('-');
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
                default_load(hash_sourcecat, hash_category_id, page);
            }, 100);
        }
    }

    function default_load(sourcecat, category, page) {
        var pathname = window.location.pathname;
        var container = $(".dropfiles-content-default.dropfiles-content-multi[data-category=" + sourcecat + "]");
        if (container.length == 0) {
            return;
        }
        $(document).trigger('dropfiles:category-loading');
        $(".dropfiles-content-default.dropfiles-content-multi[data-category=" + sourcecat + "]").find('#current_category').val(category);
        $(".dropfiles-content-default.dropfiles-content-multi[data-category=" + sourcecat + "] .dropfiles-container-default").empty();
        $(".dropfiles-content-default.dropfiles-content-multi[data-category=" + sourcecat + "] .dropfiles-container-default").html($('#dropfiles-loading-wrap').html());

        //Get categories
        $.ajax({
            url: dropfilesBaseUrl + "index.php?option=com_dropfiles&view=frontcategories&format=json&id=" + category + "&top=" + sourcecat,
            dataType: "json"
        }).done(function (categories) {
            if (page != null) {
                window.history.pushState('', document.title, pathname + '#' + sourcecat + '-' + category + '-' + categories.category.alias + '-p' + page);
            } else {
                window.history.pushState('', document.title, pathname + '#' + sourcecat + '-' + category + '-' + categories.category.alias);
            }
            $(".dropfiles-content-default.dropfiles-content-multi[data-category=" + sourcecat + "]").find('#current_category_slug').val(categories.category.alias);

            var sourcecategories =  $(".dropfiles-content-default.dropfiles-content-multi[data-category=" + sourcecat + "]").parent().find("#dropfiles-template-default-categories-"+sourcecat).html();
            if (sourcecategories) {
                var template = Handlebars.compile(sourcecategories);
                var html = template(categories);
                $(".dropfiles-content-default.dropfiles-content-multi[data-category=" + sourcecat + "] .dropfiles-container-default").prepend(html);
            }

            for (i = 0; i < categories.categories.length; i++) {
                cParents[categories.categories[i].id] = categories.categories[i];
            }

            default_breadcrum(sourcecat, category);
            default_initClick();
            initManageFile(sourcecat);

            if (tree.length) {
                var currentTree = container.find('.dropfiles-foldertree-default');
                currentTree.find('li').removeClass('selected');
                currentTree.find('i.zmdi').removeClass('zmdi-folder').addClass("zmdi-folder");

                currentTree.jaofoldertree('open', category, currentTree);

                var el = currentTree.find('a[data-file="' + category + '"]').parent();
                el.find(' > i.zmdi').removeClass("zmdi-folder").addClass("zmdi-folder");

                if (!el.hasClass('selected')) {
                    el.addClass('selected');
                }

            }
        });

        //Get files
        $.ajax({
            url: dropfilesBaseUrl + "index.php?option=com_dropfiles&view=frontfiles&format=json&id=" + category,
            dataType: "json"
        }).done(function (content) {
            var sourcefiles =  $(".dropfiles-content-default.dropfiles-content-multi[data-category=" + sourcecat + "]").parent().find("#dropfiles-template-default-files-"+sourcecat).html();
            if (sourcefiles) {
                sourcefiles = fixJoomlaSef(sourcefiles);
                var template = Handlebars.compile(sourcefiles);
                var html = template(content);
                $(".dropfiles-content-default.dropfiles-content-multi[data-category=" + sourcecat + "] .dropfiles-container-default").append(html);
            }

            if (typeof(dropfilesColorboxInit) !== 'undefined') {
                dropfilesColorboxInit();
            }
            dropfiles_remove_loading($(".dropfiles-content-default.dropfiles-content-multi[data-category=" + sourcecat + "] .dropfiles-container-default"));
            $(".dropfiles-content-default.dropfiles-content-multi[data-category=" + sourcecat + "] .dropfilesSelectedFiles").remove();
            $(".dropfiles-content-default.dropfiles-content-multi[data-category=" + sourcecat + "] .default-download-selected").remove();
            // Check to hide download all
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
            if ($(".dropfiles-content-default.dropfiles-content-multi[data-category=" + sourcecat + "] #current-category-link").length) {
                var current_download_link = $(".dropfiles-content-default.dropfiles-content-multi[data-category=" + sourcecat + "] #current-category-link").val().toLowerCase();
                if ($(".dropfiles-content-default.dropfiles-content-multi[data-category=" + sourcecat + "] .default-download-category").length) {
                    var root_download_link = $(".dropfiles-content-default.dropfiles-content-multi[data-category=" + sourcecat + "] .default-download-category").attr('href').toLowerCase();
                    if (current_download_link !== root_download_link) {
                        $(".dropfiles-content-default.dropfiles-content-multi[data-category=" + sourcecat + "] .default-download-category").attr('href', current_download_link);
                    }
                }
            }
        });
        $(document).trigger('dropfiles:category-loaded');
    }

    function default_breadcrum(sourcecat, catid) {

        var links = [];
        current_Cat = cParents[catid];

        if (typeof (current_Cat) == 'undefined') {
            // todo: made breadcrumb working when reload page with hash
            return;
        }
        links.unshift(current_Cat);

        if (typeof(current_Cat.parent_id) != 'undefined') {
            while (cParents[current_Cat.parent_id]) {
                current_Cat = cParents[current_Cat.parent_id];
                links.unshift(current_Cat);
            }
            ;
        }

        html = '';
        for (i = 0; i < links.length; i++) {
            if (i < links.length - 1) {
                html += '<li><a class="catlink" data-idcat="' + links[i].id + '" href="javascript:void(0)">' + links[i].title + '</a><span class="divider"> &gt; </span></li>';
            } else {
                html += '<li><span>' + links[i].title + '</span></li>';
            }
        }
        $(".dropfiles-content-default.dropfiles-content-multi[data-category=" + sourcecat + "] .dropfiles-breadcrumbs-default li").remove();
        $(".dropfiles-content-default.dropfiles-content-multi[data-category=" + sourcecat + "] .dropfiles-breadcrumbs-default").append(html);

    }

    if (tree.length) {
        tree.each(function () {
            var topCat = $(this).parents('.dropfiles-content-default.dropfiles-content-multi').data('category');
            var rootCatName = $(this).parents('.dropfiles-content-default.dropfiles-content-multi').data('category-name');

            $(this).jaofoldertree({
                script: dropfilesBaseUrl + 'index.php?option=com_dropfiles&task=frontfile.getSubs&tmpl=component',
                usecheckboxes: false,
                root: topCat,
                showroot: rootCatName,
                onclick: function (elem, file) {
                    topCat = $(elem).parents('.dropfiles-content-default.dropfiles-content-multi').data('category');
                    if (topCat != file) {

                        $('.directory', $(elem).parents('.dropfiles-content-default.dropfiles-content-multi')).each(function() {
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
                                if (typeof cParents[category.data('file')] == 'undefined') {
                                    cParents[category.data('file')] = {
                                        parent_id: parent.data('parent_id'),
                                        id: category.data('file'),
                                        title: category.text()
                                    };
                                }
                            }
                        });

                    }

                    default_load(topCat, file, null);
                }
            });
        });

    }

    function initManageFile(sourcecat) {
        if (typeof sourcecat == 'undefined') {
            sourcecat = $('.dropfiles-content-default.dropfiles-content-multi').data('category');
        }
        var current_category = $(".dropfiles-content-default.dropfiles-content-multi[data-category=" + sourcecat + "]").find('#current_category').val();
        var link_manager = $(".dropfiles-content-default.dropfiles-content-multi[data-category=" + sourcecat + "]").find('.openlink-manage-files').data('urlmanage');
        link_manager = link_manager + '&task=site_manage&site_catid=' + current_category + '&tmpl=dropfilesfrontend';
        $(".dropfiles-content-default.dropfiles-content-multi[data-category=" + sourcecat + "]").find('.openlink-manage-files').attr('href', link_manager);
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
