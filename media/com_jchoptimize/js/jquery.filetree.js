// jQuery File Tree Plugin
//
// Version 1.01
//
// Cory S.N. LaViska
// A Beautiful Site (http://abeautifulsite.net/)
// 24 March 2008
//
// Visit http://abeautifulsite.net/notebook.php?article=58 for more information
//
// Usage: $('.fileTreeDemo').fileTree( options, callback )
//
// Options:  root           - root folder to display; default = /
//           script         - location of the serverside AJAX file to use; default = jqueryFileTree.php
//           folderEvent    - event to trigger expand/collapse; default = click
//           expandSpeed    - default = 500 (ms); use -1 for no animation
//           collapseSpeed  - default = 500 (ms); use -1 for no animation
//           expandEasing   - easing function to use on expand (optional)
//           collapseEasing - easing function to use on collapse (optional)
//           multiFolder    - whether or not to limit the browser to one subfolder at a time
//           loadMessage    - Message to display while initial tree loads (can be HTML)
//
// History:
//
// 1.01 - updated to work with foreign characters in directory/file names (12 April 2008)
// 1.00 - released (24 March 2008)
//
// TERMS OF USE
// 
// This plugin is dual-licensed under the GNU General Public License and the MIT License and
// is copyright 2008 A Beautiful Site, LLC. 
//
if (jQuery)
        (function ($) {

                $.extend($.fn, {
                        fileTree: function (o, h) {
                                // Defaults
                                if (!o)
                                        var o = {};
                                if (o.root == undefined)
                                        o.root = '/';
                                if (o.script == undefined)
                                        o.script = 'jqueryFileTree.php';
                                if (o.folderEvent == undefined)
                                        o.folderEvent = 'click';
                                if (o.expandSpeed == undefined)
                                        o.expandSpeed = 500;
                                if (o.collapseSpeed == undefined)
                                        o.collapseSpeed = 500;
                                if (o.expandEasing == undefined)
                                        o.expandEasing = null;
                                if (o.collapseEasing == undefined)
                                        o.collapseEasing = null;
                                if (o.multiFolder == undefined)
                                        o.multiFolder = true;
                                if (o.loadMessage == undefined)
                                        o.loadMessage = 'Loading...';

                                $(this).each(function () {

                                        function showTree(c, t, i) {
                                                $(c).addClass('wait');
                                                $(".jqueryFileTree.start").remove();
						$.ajax({
                                                        type: "GET",
                                                        url: o.script,
                                                        data: 'dir=' + t + '&jchview=tree&initial=' + i,
                                                        success: function (data) {
                                                                $(c).find('.start').html('');
                                                                $(c).removeClass('wait').append(data);
                                                                if (o.root == t)
                                                                        $(c).find('UL:hidden').show();
                                                                else
                                                                        $(c).find('UL:hidden').slideDown({
                                                                                duration: o.expandSpeed,
                                                                                easing: o.expandEasing
                                                                        });
                                                                bindTree(c);
                                                        }
                                                });
                                               // $.post(o.script, {dir: t, jchview: 'tree', initial: i}, function (data) {
                                               //         $(c).find('.start').html('');
                                               //         $(c).removeClass('wait').append(data);

                                               //         if (o.root == t)
                                               //                 $(c).find('UL:hidden').show();
                                               //         else
                                               //                 $(c).find('UL:hidden').slideDown({duration: o.expandSpeed, easing: o.expandEasing});
                                               //         bindTree(c);


                                               // });
                                        }

                                        function bindTree(t) {
                                                $(t).find('LI A').bind(o.folderEvent, function () {
                                                        openFolder(this);
                                                });
                                                // Prevent A from triggering the # on non-click events
                                                if (o.folderEvent.toLowerCase != 'click')
                                                        $(t).find('LI A').bind('click', function () {
                                                                return false;
                                                        });
                                        }

                                        function openFolder(obj)
                                        {
                                                if ($(obj).parent().hasClass('directory')) {
                                                        if($(obj).data('url') == ''){
                                                                if (!o.multiFolder) {
                                                                        $(obj).parent().find('UL').find('ul').slideUp({
                                                                                duration: o.collapseSpeed, easing: o.collapseEasing});
                                                                        $(obj).parent().find('LI.directory').removeClass('expanded').addClass('collapsed');
                                                                }
                                                               $('#files-container').load(o.script, 'dir='+escape($(obj).data('url')),
                                                                function () {
                                                                        bindDirList();
                                                                }); 
                                                        }else if ($(obj).parent().hasClass('collapsed')) {
                                                                // Expand
                                                                if (!o.multiFolder) {
                                                                        $(obj).parent().parent().find('UL').slideUp({
                                                                                duration: o.collapseSpeed, easing: o.collapseEasing});
                                                                        $(obj).parent().parent().find('LI.directory').removeClass('expanded').addClass('collapsed');
                                                                }
                                                                $(obj).parent().find('UL').remove(); // cleanup
                                                                showTree($(obj).parent(), escape($(obj).data('url')), '0');
                                                                $(obj).parent().removeClass('collapsed').addClass('expanded');

                                                                $('#files-container').load(o.script, 'dir='+escape($(obj).data('url')),
                                                                function () {
                                                                        bindDirList();
                                                                });
                                                        } else {
                                                                // Collapse
                                                                $(obj).parent().find('UL').slideUp({duration: o.collapseSpeed, easing: o.collapseEasing});
                                                                $(obj).parent().removeClass('expanded').addClass('collapsed');
                                                        }
                                                } else {
                                                        h($(obj).data('url'));
                                                }
                                                return false;
                                        }

                                        function bindDirList()
                                        {
                                                $('#files-container').find('li a').bind('click', function () {
                                                        return false;
                                                });

                                                $('#files-container').find('li a').bind('dblclick', function () {
                                                        var grandparent = $("#file-tree-container ul.jqueryFileTree").find("li.expanded").last();
                                                        var anchor = $(grandparent).find('li a[data-url="' + $(this).data('url') + '"]');

                                                        openFolder(anchor);
                                                });
                                        }
                                        // Loading message
                                        $(this).html('<ul class="jqueryFileTree start"><li class="wait">' + o.loadMessage + '<li></ul>');
                                        // Get the initial file list
                                        showTree($(this), escape(o.root), '1');

                                        $('#files-container').load(o.script, 'dir='+escape(o.root), function () {
                                                bindDirList();
                                        });

                                });
                        }
                });

        })(jQuery);

jQuery(document).ready(function () {
        jQuery('#optimize-images-container').on('click', '.jqueryFileTree li.check-all input[type=checkbox]', function (event) {
                jQuery('li.directory input[type="checkbox"],li.file input[type="checkbox"]').prop('checked', this.checked)
        });
});
