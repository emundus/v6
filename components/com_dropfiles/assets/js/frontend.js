
jQuery(document).ready(function($) {

    function preloader() {
        $('.dropfiles-loading').css('background',"url("+dropfilesBaseUrl+ "components/com_dropfiles/assets/images/loadingfile.svg) no-repeat center center");
    }
    function addLoadEvent(func) {
        var oldonload = window.onload;
        if (typeof window.onload != 'function') {
            window.onload = func;
        } else {
            window.onload = function() {
                if (oldonload) {
                    oldonload();
                }
                func();
            }
        }
    }

    addLoadEvent(preloader);

    $(document).on('click', '.dropfiles-open-tree', function (e) {
        var $this = $(this);
        var tree = $this.parent().find('.dropfiles-foldertree');

        // tree.toggleClass('tree-open');
        if (tree.hasClass('tree-open')) {
            tree.slideUp(500).removeClass('tree-open');
        } else {
            tree.slideDown(500).addClass('tree-open');
        }
    });
    $(document).on('dropfiles:category-loading', function (e) {
        var tree2 = $('.dropfiles-foldertree');
        // Hide all opened left tree
        if (tree2.hasClass('tree-open')) {
            tree2.slideUp(500).removeClass('tree-open');
        }
    });
});

function dropfiles_remove_loading(el) {
    jQuery('.dropfiles-loading', el).remove();
}
