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
    var sourcefiles = $("#dropfiles-template-ggd-files").html();
    var sourcecategories = $("#dropfiles-template-ggd-categories").html();
    var sourcefile = $("#dropfiles-template-ggd-box").html();
    var gdd_hash = window.location.hash;
    var ggd_cParents = {};
    var ggd_tree = $('.dropfiles-foldertree-ggd');

    if (typeof sourcefiles != 'undefined' && sourcefiles != null) {
        var reg = new RegExp(dropfilesRootUrl + "{{", 'g');
        sourcefile = sourcefile.replace(reg, "{{");
        sourcefiles = sourcefiles.replace(reg, "{{");
    }

    var ggd_root_cat = $('.dropfiles-content-ggd').data('category');

    $(".dropfiles-content-ggd").each(function (index) {
        var ggd_topCat = $(this).data('category');
        ggd_cParents[ggd_topCat] = {parent_id: 0, id: ggd_topCat, title: $(this).find("h2").text()};

        $(this).find(".dropfilescategory.catlink").each(function (index) {
            var tempidCat = $(this).data('idcat');
            ggd_cParents[tempidCat] = {parent_id: ggd_topCat, id: tempidCat, title: $(this).text()};
        })
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

    initClickFile();
    gdd_hash = gdd_hash.replace('#', '');
    if (gdd_hash != '') {
        var hasha = gdd_hash.split('-');
        var re = new RegExp("^(p[0-9]+)$");
        var page = null;
        var stringpage = hasha.pop();

        if (re.test(stringpage)) {
            page = stringpage.replace('p', '');
        }

        var hash_category_id = hasha[0];
        if (!parseInt(hash_category_id)) {
            return;
        }
        setTimeout(function () {
            load($('.dropfiles-content-ggd').data('category'), hash_category_id, page);
        }, 100)
    }

    function ggd_initClick() {
        $('.dropfiles-content-ggd.dropfiles-content-multi .catlink').click(function (e) {
            e.preventDefault();
            load($(this).parents('.dropfiles-content-ggd.dropfiles-content-multi').data('category'), $(this).data('idcat'), null);
        });
    }

    ggd_initClick();
    initManageFile($('.dropfiles-content-ggd.dropfiles-content-multi .catlink').parents('.dropfiles-content-ggd.dropfiles-content-multi').data('category'));

    function initClickFile() {
        $('.dropfiles-content-ggd.dropfiles-content .dropfiles-file-link').unbind('click').click(function (e) {
            var href = $(this).attr('href');
            if (href !== '#') {
                return;
            }
            e.preventDefault();
            fileid = $(this).data('id');
            catid = $(this).parents(".dropfiles-content-ggd").data('current');
            $.ajax({
                url: dropfilesBaseUrl + "index.php?option=com_dropfiles&view=frontfile&format=json&id=" + fileid + "&catid=" + catid,
                dataType: "json"
            }).done(function (file) {
                var template = Handlebars.compile(sourcefile);
                var html = template(file);
                box = $("#dropfiles-box-ggd");
                if (box.length === 0) {
                    $('body').append('<div id="dropfiles-box-ggd" style="display: none;"></div>');
                    box = $("#dropfiles-box-ggd");
                }
                box.empty();
                box.prepend(html);
                box.click(function (e) {
                    if ($(e.target).is('#dropfiles-box-ggd')) {
                        box.hide();
                    }
                    $('#dropfiles-box-ggd').unbind('click.box').bind('click.box', function (e) {
                        if ($(e.target).is('#dropfiles-box-ggd')) {
                            box.hide();
                        }
                    });
                });
                $('#dropfiles-box-ggd .dropfiles-close').click(function (e) {
                    e.preventDefault();
                    box.hide();
                });
                if (typeof(dropfilesColorboxInit) !== 'undefined') {
                    dropfilesColorboxInit();
                }
                box.show();

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

    function load(sourcecat, category, page) {
        var pathname = window.location.pathname;
        $(document).trigger('dropfiles:category-loading');
        $(".dropfiles-content-ggd.dropfiles-content-multi[data-category=" + sourcecat + "]").find('#current_category').val(category);
        $(".dropfiles-content-ggd.dropfiles-content-multi[data-category=" + sourcecat + "] .dropfiles-container-ggd").empty();
        $(".dropfiles-content-ggd.dropfiles-content-multi[data-category=" + sourcecat + "] .dropfiles-container-ggd").html($('#dropfiles-loading-wrap').html());

        //Get categories
        $.ajax({
            url: dropfilesBaseUrl + "index.php?option=com_dropfiles&view=frontcategories&format=json&id=" + category + "&top=" + sourcecat,
            dataType: "json"
        }).done(function (categories) {

            if (page != null) {
                window.history.pushState('', document.title, pathname + '#' + category + '-' + categories.category.alias + '-p' + page);
            } else {
                window.history.pushState('', document.title, pathname + '#' + category + '-' + categories.category.alias);
            }
            $(".dropfiles-content-ggd.dropfiles-content-multi[data-category=" + sourcecat + "]").find('#current_category_slug').val(categories.category.alias);

            var template = Handlebars.compile(sourcecategories);
            var html = template(categories);
            $(".dropfiles-content-ggd.dropfiles-content-multi[data-category=" + sourcecat + "]").data('current', category);
            $(".dropfiles-content-ggd.dropfiles-content-multi[data-category=" + sourcecat + "] .dropfiles-container-ggd").prepend(html);

            for (i = 0; i < categories.categories.length; i++) {
                ggd_cParents[categories.categories[i].id] = categories.categories[i];
            }

            ggd_breadcrum(sourcecat, category);
            ggd_initClick();
            initManageFile(sourcecat);
            if (ggd_tree.length) {

                ggd_tree.find('li').removeClass('selected');
                ggd_tree.find('i.zmdi').removeClass('zmdi-folder').addClass("zmdi-folder");

                ggd_tree.jaofoldertree('open', category);

                var el = ggd_tree.find('a[data-file="' + category + '"]').parent();
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
            var template = Handlebars.compile(sourcefiles);
            var html = template(content);
            $(".dropfiles-content-ggd.dropfiles-content-multi[data-category=" + sourcecat + "] .dropfiles-container-ggd").append(html);
            initClickFile();
            if (typeof(dropfilesColorboxInit) !== 'undefined') {
                dropfilesColorboxInit();
            }
            dropfiles_remove_loading($(".dropfiles-content-ggd.dropfiles-content-multi[data-category=" + sourcecat + "] .dropfiles-container-ggd"));
        });
        $(document).trigger('dropfiles:category-loaded');
    }

    function ggd_breadcrum(sourcecat, catid) {
        links = [];
        current_Cat = ggd_cParents[catid];

        if (typeof (current_Cat) == 'undefined') {
            // todo: made breadcrumb working when reload page with hash
            return;
        }

        links.unshift(current_Cat);
        if (current_Cat.parent_id != 0) {
            while (ggd_cParents[current_Cat.parent_id]) {
                current_Cat = ggd_cParents[current_Cat.parent_id];
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
        $(".dropfiles-content-ggd[data-category=" + sourcecat + "] .dropfiles-breadcrumbs-ggd li").remove();
        $(".dropfiles-content-ggd[data-category=" + sourcecat + "] .dropfiles-breadcrumbs-ggd").append(html);

    }

    if (ggd_tree.length) {
        ggd_tree.each(function (index) {
            var ggd_topCat = $(this).parents('.dropfiles-content-ggd.dropfiles-content-multi').data('category');
            var rootCatName = $(this).parents('.dropfiles-content-ggd.dropfiles-content-multi').data('category-name');
            $(this).jaofoldertree({
                script: dropfilesBaseUrl + 'index.php?option=com_dropfiles&task=frontfile.getSubs&tmpl=component',
                usecheckboxes: false,
                root: ggd_topCat,
                showroot: rootCatName,
                onclick: function (elem, file) {
                    ggd_topCat = $(elem).parents('.dropfiles-content-ggd.dropfiles-content-multi').data('category');
                    if (ggd_topCat != file) {

                        $(elem).parents('.directory').each(function () {
                            var $this = $(this);
                            var category = $this.find(' > a');
                            var parent = $this.find('.icon-open-close');
                            if (parent.length > 0) {
                                if (typeof ggd_cParents[category.data('file')] == 'undefined') {
                                    ggd_cParents[category.data('file')] = {
                                        parent_id: parent.data('parent_id'),
                                        id: category.data('file'),
                                        title: category.text()
                                    };
                                }
                            }
                        });

                    }

                    load(ggd_topCat, file, null);
                }
            });
        })
    }

    function initManageFile(sourcecat) {
        if (typeof sourcecat == 'undefined') {
            sourcecat = $('.dropfiles-content-ggd.dropfiles-content-multi').data('category');
        }
        var current_category = $(".dropfiles-content-ggd.dropfiles-content-multi[data-category=" + sourcecat + "]").find('#current_category').val();
        var link_manager = $(".dropfiles-content-ggd.dropfiles-content-multi[data-category=" + sourcecat + "]").find('.openlink-manage-files').data('urlmanage');
        link_manager = link_manager + '&task=site_manage&site_catid=' + current_category + '&tmpl=dropfilesfrontend';
        $(".dropfiles-content-ggd.dropfiles-content-multi[data-category=" + sourcecat + "]").find('.openlink-manage-files').attr('href', link_manager);
    }

});
