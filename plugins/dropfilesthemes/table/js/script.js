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
    })

    //load media tables
    $('.dropfiles-content-table.dropfiles-content .mediaTable').mediaTable();

    var source = $("#dropfiles-template-table").html();
    var tpltable_sourcecategories = $("#dropfiles-template-table-categories").html();

    if (typeof source != 'undefined' && source != null) {
        source = source.replace(dropfilesRootUrl, "");
        var reg = new RegExp("/{{", 'g');
        source = source.replace(reg, "{{");
    }

    Handlebars.registerHelper('bytesToSize', function (bytes) {
        return bytesToSize(bytes);
    });

    function initClick() {
        $('.dropfiles-content-table.dropfiles-content-multi .catlink').click(function (e) {
            e.preventDefault();
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
        var hash_category_id = hasha[0];
        if (!parseInt(hash_category_id)) {
            return;
        }
        setTimeout(function () {
            table_load($('.dropfiles-content-table').data('category'), hash_category_id, page);
        }, 100)
    }

    initManageFile($('.dropfiles-content-table.dropfiles-content-multi .catlink').parents('.dropfiles-content-default.dropfiles-content-multi').data('category'));

    function table_load(sourcecat, category, page) {
        var pathname = window.location.pathname;
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

            if (page != null) {
                window.history.pushState('', document.title, pathname + '#' + category + '-' + categories.category.alias + '-p' + page);
            } else {
                window.history.pushState('', document.title, pathname + '#' + category + '-' + categories.category.alias);
            }

            $(".dropfiles-content-table.dropfiles-content-multi[data-category=" + sourcecat + "]").find('#current_category_slug').val(categories.category.alias);
            var template = Handlebars.compile(tpltable_sourcecategories);
            var html = template(categories);
            dropfiles_remove_loading($(".dropfiles-content-table.dropfiles-content-multi[data-category=" + sourcecat + "] .dropfiles-categories"));
            $(".dropfiles-content-table.dropfiles-content-multi[data-category=" + sourcecat + "] .dropfiles-categories").prepend(html);

            if (table_tree.length) {

                table_tree.find('li').removeClass('selected');
                table_tree.find('i.zmdi').removeClass('zmdi-folder').addClass("zmdi-folder");

                table_tree.jaofoldertree('open', category);

                var el = table_tree.find('a[data-file="' + category + '"]').parent();
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
                var template = Handlebars.compile(source);
                var html = template(content);
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
});
