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
 *
 */
jQuery(document).ready(function ($) {
    if (typeof(Dropfiles) == 'undefined') {
        Dropfiles = {};
        Dropfiles.can = {};
        Dropfiles.can.create = false;
        Dropfiles.can.edit = false;
        Dropfiles.can.delete = false;
        Dropfiles.maxfilesize = 10;
        Dropfiles.selection = {};
        Dropfiles.selected = {};
        Dropfiles.selected.access = false;
        Dropfiles.selected.ordering = false;
        Dropfiles.selected.orderingdir = false;
        Dropfiles.selected.usergroup = false;
        Dropfiles.catRefTofileId = false;
    }

    if (typeof (Joomla) === "undefined") {
        return;
    }

    var leftwidth = parseInt($("#mycategories").width());
    $("#mycategories").resizable({handles: "e"}).resize(function () {
        var width = parseInt(this.style.width);
        return this.style['-webkit-flex-basis'] = (width - leftwidth) + 'px';
    });

    /**
     * Click to Sync with Google Drive
     */
    $('#btn-sync-gg').click(function (e) {
        e.preventDefault();
        var $btn = $(this);
        var syncText = $btn.html();
        $btn.html('Syncing');
        $btn.attr('disabled', true);
        $.ajax({
            url: 'index.php?option=com_dropfiles&task=googledrive.syncRoot'
        }).done(function (data) {
            console.log(data);
            if(data.status) {
                syncGoogleCategories();
            } else {
                if (data.type === 'confirm') {
                    bootbox.confirm(data.message, function (result) {
                        if (result === true) {
                            // Stop other sync then do the sync again
                            $.ajax({
                                url: 'index.php?option=com_dropfiles&task=googledrive.abortsync'
                            }).done(function(data){
                                if (data.status) {
                                    var checkStatusInterval = setInterval(function(){
                                        $.ajax({
                                            url: 'index.php?option=com_dropfiles&task=googledrive.checkSyncStatus'
                                        }).done(function(data){
                                            if (data.status) {
                                                clearInterval(checkStatusInterval);
                                                $('#btn-sync-gg').trigger('click');
                                            }
                                        });
                                    }, 3000);
                                }
                            });
                        }
                    });
                }

                $btn.html(syncText);
                $btn.attr('disabled', false);
            }
        });
    });

    //sync google folders
    function syncGoogleCategories(step) {
        if (typeof(step) == 'undefined') {
            step = 0;
        } else {
            step = parseInt(step);
        }
        $.ajax({
            url: Dropfiles.ajaxurl + 'index.php?option=com_dropfiles&task=frontgoogle.syncFolders&ran='+ Math.random() + '&step=' + step, // random() to avoid cache in some sever
        }).done(function (data) {
            result = jQuery.parseJSON(data);
            if (result.continue) {
                step++;
                syncGoogleCategories(step);
            } else {
                console.log("sync folders done");
                syncGoogleFiles();
            }

        });
    }

    //sync google files
    function syncGoogleFiles(step, category_id) {
        if (typeof(step) == 'undefined') {
            step = 0;
        } else {
            step = parseInt(step);
        }
        if (typeof(category_id) == 'undefined') {
            category_id = 0;
        } else {
            category_id = parseInt(category_id);
        }
        $.ajax({
            url: Dropfiles.ajaxurl + 'index.php?option=com_dropfiles&task=frontgoogle.syncFiles&ran='+ Math.random() + '&step=' + step+'&catid='+category_id,
        }).done(function (data) {
            result = jQuery.parseJSON(data);
            if (result.continue) {
                step++;
                syncGoogleFiles(step, category_id);
            } else {
                // business logic...
                $('#btn-sync-gg').button('reset');
                window.location.reload();
            }

        });
    }

    /**
     * Click to Sync with Dropbox
     */
    $('#btn-sync-dropbox').click(function (e) {
        e.preventDefault();
        var $btn = $(this);
        var syncText = $btn.html();

        $btn.html('Syncing');
        $btn.attr('disabled', true);

        $.ajax({
            url: 'index.php?option=com_dropfiles&task=dropbox.sync',
            dataType: "json"
        }).done(function (data) {
            if (data.status) {
                syncDropboxFiles();
            } else {
                $btn.html(syncText);
                $btn.attr('disabled', false);
            }
        });
    });

    function syncDropboxFiles(step) {
        if (typeof(step) == 'undefined') {
            step = 0;
        } else {
            step = parseInt(step);
        }
        $.ajax({
            url: Dropfiles.ajaxurl + 'index.php?option=com_dropfiles&task=frontdropbox.syncFiles' + '&step=' + step,
        }).done(function (data) {
            result = jQuery.parseJSON(data);
            if (result.continue) {
                step++;
                syncDropboxFiles(step);
            } else {
                // business logic...
                $('#btn-sync-dropbox').button('reset');
                window.location.reload();
            }

        });
    }

    /**
     * Click to Sync with OneDrive
     */
    $('#btn-sync-onedrive').click(function (e) {
        e.preventDefault();
        var $btn = $(this).html('Syncing');
        $(this).attr('disabled', true);
        $.ajax({
            url: 'index.php?option=com_dropfiles&task=onedrive.onedrivesync'
        }).done(function (data) {
            $.ajax({
                url: Dropfiles.ajaxurl + 'index.php?option=com_dropfiles&task=frontonedrive.index',
                type: 'POST',
                data: {}
            }).done(function (data) {
                window.location.reload();
            });
            // business logic...
            $btn.button('reset');
        });
    });

    /**
     * Click to Sync with OneDrive Business
     */
    $('#btn-sync-onedrive-business').click(function (e) {
        e.preventDefault();
        var $btn = $(this).html('Syncing');
        $(this).attr('disabled', true);
        $.ajax({
            url: 'index.php?option=com_dropfiles&task=onedrivebusiness.oneDriveBusinessSync'
        }).done(function (data) {
            $.ajax({
                url: Dropfiles.ajaxurl + 'index.php?option=com_dropfiles&task=frontonedrivebusiness.index',
                type: 'POST',
                data: {}
            }).done(function (data) {
                window.location.reload();
            });
            // business logic...
            $btn.button('reset');
        });
    });

    /**
     * Left side panel show/hide
     */
    // $('#df-panel-toggle').toggle(
    //     function (e) {
    //         e.preventDefault();
    //         $('#mycategories').animate({left: -305}, 300);
    //         if ($('#mybootstrap').parent().prop('tagName') === 'DIV') {
    //             $('#pwrapper').css({'margin-left': 0});
    //         }
    //         else {
    //             $('#pwrapper').css({'margin-left': 12});
    //         }
    //         $('#df-panel-toggle span').css({'right': '-25px'}).removeClass('icon-arrow-left-2').addClass('icon-arrow-right-2');
    //     },
    //     function (e) {
    //         console.log(e);
    //         e.preventDefault();
    //         if ($('#mybootstrap').parent().prop('tagName') === 'DIV') {
    //             $('#mycategories').animate({left: 20}, 300);
    //         }
    //         else {
    //             $('#mycategories').animate({left: 0}, 300);
    //         }
    //         $('#pwrapper').css({'margin-left': 320});
    //         $('#df-panel-toggle span').css({'right': '0px'}).removeClass('icon-arrow-right-2').addClass('icon-arrow-left-2');
    //     }
    // );

    var categoryAjax = null;
    Dropfiles.checkAndUpdatePreview = checkAndUpdatePreview = function () {
        var catsmanage = getUrlParameter('site_catid');
        var tasksmanage = getUrlParameter('task');
        if (!tasksmanage && tasksmanage !== 'site_manage') {
            updatepreview();
        } else {
            updatepreview(catsmanage);
        }
    }

    /**
     * Init sortable files
     * Save order after each sort
     */
    function initSortableFiles() {
        $('#preview').sortable({
            placeholder: 'highlight file',
            revert: 300,
            distance: 5,
            tolerance: "pointer",
            items: ".file",
            appendTo: "body",
            cursorAt: {top: 0, left: 0},
            helper: function (e, item) {
                filename = $(item).find('.title').text() + "." + $(item).find('.type').text();
                fileext = $(item).find('.type').text();
                count = $('#preview').find('.file.selected').length;
                if (count > 1) {
                    return $("<div id='file-handle' class='dropfiles_draged_file ui-widget-header' ><div class='ext "+fileext+"'><span class='txt'>"+fileext+"</span></div><div class='filename'>" + filename + "</div><span class='fCount'>" + count + "</span></div>");
                } else {
                    return $("<div id='file-handle' class='dropfiles_draged_file ui-widget-header' ><div class='ext "+fileext+"'><span class='txt'>"+fileext+"</span></div><div class='filename'>" + filename + "</div></div>");
                }
            },
            update: function () {
                var json = '';
                id_category = jQuery('input[name=id_category]').val();
                $.each($('#preview .file'), function (i, val) {
                    if (json !== '') {
                        json += ',';
                    }
                    json += '"' + i + '":"' + $(val).data('id-file') + '"';
                });
                json = '{' + json + '}';
                $.ajax({
                    url: "index.php?option=com_dropfiles&task=files.reorder&idcat=" + id_category,
                    type: "POST",
                    data: {order: json}
                }).done(function (data) {
                    var ismovefile = $('#dropfiles-movefile-container');
                    if (ismovefile.length > 0) {
                        ismovefile.remove();
                    } else {
                        $.gritter.add({text: Joomla.JText._('COM_DROPFILES_JS_CATEGORY_ORDER', 'File(s) removed with success!')});
                    }
                });
            },
            /** Prevent firefox bug positionnement **/
            start: function (event, ui) {
                $(ui.helper).css('width', 'auto');
//                $(ui.helper).find('td').each(function(i,e){
//                    $(e).css('width',$('#preview .restable thead th:nth-child('+(i+1)+')').width());
//                });

                var userAgent = navigator.userAgent.toLowerCase();
                if (ui.helper !== "undefined" && userAgent.match(/firefox/)) {
                    ui.helper.css('position', 'absolute');
                }

                ui.placeholder.html("<td colspan='8'></td>");
                //ui.placeholder.height(ui.helper.height());
            },
            beforeStop: function (event, ui) {
                var userAgent = navigator.userAgent.toLowerCase();
                if (ui.offset !== "undefined" && userAgent.match(/firefox/)) {
                    ui.helper.css('margin-top', 0);
                }
            }
        });
    }

    $('#preview').disableSelection();

    /* init menu actions */
    initMenu();

    initThemeBtn();

    /* Load category */
//updatepreview();
    //Check what is loaded via editor
    if (typeof(gcaninsert) !== 'undefined' && gcaninsert === true) {
        if (typeof(window.parent.tinyMCE) !== 'undefined' && window.parent.tinyMCE.activeEditor !== null) {
            content = window.parent.tinyMCE.get(e_name).selection.getContent();
            imgparent = window.parent.tinyMCE.get(e_name).selection.getNode().parentNode;
            exp = '<img.*data\-dropfilesfile="([0-9a-zA-Z_]+)".*?>';
            file = content.match(exp);
            exp = '<img.*data\-dropfilescategory="([0-9]+)".*?>';
            category = content.match(exp);
            exp = '<img.*data\-dropfilesfilecategory="([0-9]+)".*?>';
            filecategory = content.match(exp);
            Dropfiles.selection = new Array();
            Dropfiles.selection.content = content;

            if (file !== null && filecategory !== null) {
                if (file !== null) {
                    elem = $(content).filter('img[data-dropfilesfile=' + file[1] + ']');
                    Dropfiles.selection.selection = elem;
                    Dropfiles.selection.file = file[1];
                }
                if (filecategory !== null) {
                    Dropfiles.selection.category = filecategory[1];
                    $('#categorieslist li').removeClass('active');
                    $('#categorieslist li[data-id-category="' + filecategory[1] + '"]').addClass('active');
                    $('input[name=id_category]').val(filecategory[1]);
                    updatepreview(filecategory[1], file[1]);
                }
            } else if (category !== null) {
                Dropfiles.selection.category = category[1];
                $('#categorieslist li').removeClass('active');
                $('#categorieslist li[data-id-category="' + category[1] + '"]').addClass('active');
                $('input[name=id_category]').val(category[1]);
                updatepreview(category[1]);
                loadGalleryParams();
            } else {
                updatepreview();
                loadGalleryParams();
            }
        } else if (typeof window.parent.CKEDITOR != 'undefined') {
            var ckEditor = window.parent.CKEDITOR.instances[e_name];
            imgElement = ckEditor.getSelection().getSelectedElement();
            if (typeof imgElement != "undefined" && imgElement != null) {
                file = imgElement.getAttribute('data-dropfilesfile');
                category = imgElement.getAttribute('data-dropfilescategory');
                filecategory = imgElement.getAttribute('data-dropfilesfilecategory');
                Dropfiles.selection = new Array();

                if (file !== null && filecategory !== null) {
                    if (file !== null) {
                        Dropfiles.selection.selection = imgElement;
                        Dropfiles.selection.file = file;
                    }
                    if (filecategory !== null) {
                        Dropfiles.selection.category = filecategory;
                        $('#categorieslist li').removeClass('active');
                        $('#categorieslist li[data-id-category="' + filecategory + '"]').addClass('active');
                        $('input[name=id_category]').val(filecategory);
                        updatepreview(filecategory, file);
                    }
                } else if (category !== null) {
                    Dropfiles.selection.category = category;
                    $('#categorieslist li').removeClass('active');
                    $('#categorieslist li[data-id-category="' + category + '"]').addClass('active');
                    $('input[name=id_category]').val(category);
                    updatepreview(category);
                    loadGalleryParams();
                } else {
                    updatepreview();
                    loadGalleryParams();
                }
            } else {
                updatepreview();
                loadGalleryParams();
            }
        } else {
            updatepreview();
            loadGalleryParams();
        }
    } else {
        /* Load gallery */
        checkAndUpdatePreview();
    }

  function checkCateActive(id_category) {
    id_category_ck = null;
    var listIdDisable = [];
    $('#categorieslist li').each(function (index) {
      if ($(this).hasClass('disable-cat')) {
        $(this).removeClass('active');
        listIdDisable.push($(this).data('item-disable'));
      }
      id_category_ck = $('#categorieslist li.active').data('id-category');

      if (id_category) {
        if (jQuery.inArray(id_category, listIdDisable >= 0)) {
          if (typeof(id_category_ck) == 'undefined') {
            $('#categorieslist li.not-disable-cat:first').addClass('active');
            id_category = $('#categorieslist li.active').data('id-category');
          }
        } else {
          if (typeof(id_category_ck) == 'undefined') {
            $('#categorieslist li.not-disable-cat:first').addClass('active');
            id_category = $('#categorieslist li.active').data('id-category');
          }
        }
      } else {
        if (typeof(id_category_ck) == 'undefined') {
          $('#categorieslist li.not-disable-cat:first').addClass('active');
          id_category = $('#categorieslist li.active').data('id-category');
        }
        else {
          id_category = id_category_ck;
        }
      }
    });
    $('input[name=id_category]').val(id_category);
    return id_category;
  }

    /* Load nestable */
  checkCateActive(null);
    if (Dropfiles.can.edit || (Dropfiles.can.editown && Dropfiles.author === $('#categorieslist li.active').data('author'))) {
        $('.nested').nestable({
            maxDepth: 16,
            effect: {animation: 'fade', time: 'slow'},
            onClick: function(l, e, p) {
                id_category = $(e).data('id-category');
                $('input[name=id_category]').val(id_category);
                if (Dropfiles.catRefTofileId) {
                    updatepreview(id_category, Dropfiles.catRefTofileId);
                    Dropfiles.catRefTofileId = false;
                } else {
                    updatepreview(id_category);
                    Dropfiles.catRefTofileId = false;
                }
                $('#categorieslist li').removeClass('active');
                $(e).addClass('active');
                if ($(e).find('.google-drive-icon').length > 0) {
                    $('#rightcol .fileblock').addClass('googleblock');
                    $('#rightcol .categoryblock').addClass('catgoogleblock');
                } else {
                    $('#rightcol .fileblock').removeClass('googleblock');
                    $('#rightcol .categoryblock').removeClass('catgoogleblock');
                }
                updatepreview(id_category);
                return false;
            },
            callback: function (event, e) {
                var isCloudItem = $(e).find('div.dd3-handle i.google-drive-icon-white').length;
                var isDropboxItem = $(e).find('div.dd3-handle i.dropbox-icon-white').length;
                var isOnedriveItem = $(e).find('div.dd3-handle i.onedrive-icon-white').length;
                var isOnedriveBusinessItem = $(e).find('div.dd3-handle i.onedrive-business-icon-white').length;
                var itemChangeType = 'default';
                if (isCloudItem > 0) {
                    itemChangeType = 'googledrive';
                } else if (isDropboxItem > 0) {
                    itemChangeType = 'dropbox';
                } else if (isOnedriveItem > 0) {
                    itemChangeType = 'onedrive';
                } else if (isOnedriveBusinessItem > 0) {
                    itemChangeType = 'onedrivebusiness';
                }
                pk = $(e).data('id-category');
                if ($(e).prev('li').length === 0) {
                    position = 'first-child';
                    if ($(e).parents('li').length === 0) {
                        //root
                        ref = 0;
                    } else {
                        ref = $(e).parents('li').data('id-category');
                    }
                } else {
                    position = 'after';
                    ref = $(e).prev('li').data('id-category');
                }
                $.ajax({
                    url: "index.php?option=com_dropfiles&task=categories.order&pk=" + pk + "&position=" + position + "&ref=" + ref + "&dragType=" + itemChangeType,
                    type: "POST"
                }).done(function (data) {
                    result = jQuery.parseJSON(data);
                    if (result.response === true) {
                        $.gritter.add({text: Joomla.JText._('COM_DROPFILES_JS_CATEGORY_ORDER', 'New category order saved!')});
                    } else {
                        bootbox.alert(result.response);
                    }
                });

            }
        });
        if (Dropfiles.collapse === true) {
            $('.nested').nestable('collapseAll');
        }
    }

    var ctrlDown = false;
    $(window).on("keydown", function (event) {
        if (event.which === 17 || event.ctrlKey || event.metaKey) {
            ctrlDown = true;
        }
    }).on("keyup", function (event) {
        ctrlDown = false;
    });

    catDroppable = function () {
        $("#categorieslist li.dd-item > .dd-content").droppable({
            accept: '.file',
            hoverClass: "dd-content-hover",
            tolerance: "pointer",
            drop: function (event, ui) {

                $(this).addClass("ui-state-highlight");
                cat_target = $(event.target).parent().data("id-category");
                current_cat = $("#categorieslist .dd-item.active").data('id-category');
                if (current_cat != cat_target) {
                    count = $('#preview').find('.file.selected').length;
                    if (count > 0) { //multiple file
                        iFile = 0;
                        $('#preview').find('.file.selected').each(function () {
                            id_file = $(this).data("id-file");
                            if (ctrlDown) { //copy file
                                $.ajax({
                                    url: "index.php?option=com_dropfiles&task=files.copyfile&id_category=" + cat_target + '&active_category=' + current_cat + '&id_file=' + id_file,
                                    type: "POST"
                                }).done(function (data) {
                                    iFile++;
                                    if (iFile == count) {
                                        $.gritter.add({text: Joomla.JText._('COM_DROPFILES_JS_FILES_COPIED', 'Files copied with success!')});
                                    }
                                });
                            } else {
                                $.ajax({
                                    url: "index.php?option=com_dropfiles&task=files.movefile&id_category=" + cat_target + '&active_category=' + current_cat + '&id_file=' + id_file,
                                    type: "POST",
                                    dataType: "json",
                                    beforeSend: function () {
                                        if($('#file-handle').length) {
                                            $('#file-handle').animate({
                                                width: '0',
                                                height: '0',
                                                opacity: .6
                                            }, 100, "linear", function () {
                                                $( this ).hide();
                                            });
                                        }
                                    }
                                }).done(function (result) {
                                    iFile++;
                                    if (typeof result.datas.id_file != "undefined") {
                                        $('tr[data-id-file="' + result.datas.id_file + '"]').remove();
                                    }
                                    if (iFile == count) {
                                        $.gritter.add({text: Joomla.JText._('COM_DROPFILES_JS_FILES_MOVED', 'Files moved with success!')});
                                        var ismoved = '<div id="dropfiles-movefile-container" style="display: none"></div>';
                                        $('#mybootstrap').append(ismoved);
                                    }
                                });
                            }
                        })
                    }
                    else {  //single file
                        id_file = $(ui.draggable).data("id-file");
                        if (ctrlDown) { //copy file
                            $.ajax({
                                url: "index.php?option=com_dropfiles&task=files.copyfile&id_category=" + cat_target + '&active_category=' + current_cat + '&id_file=' + id_file,
                                type: "POST"
                            }).done(function (data) {
                                $.gritter.add({text: Joomla.JText._('COM_DROPFILES_JS_FILE_COPIED', 'File copied with success!')});
                            });
                        } else {
                            $.ajax({
                                url: "index.php?option=com_dropfiles&task=files.movefile&id_category=" + cat_target + '&active_category=' + current_cat + '&id_file=' + id_file,
                                type: "POST",
                                beforeSend: function () {
                                    if($('#file-handle').length) {
                                        $('#file-handle').animate({
                                            width: '0',
                                            height: '0',
                                            opacity: .6
                                        }, 100, "linear", function () {
                                            $( this ).hide();
                                        });
                                    }
                                }
                            }).done(function (data) {
                                $('tr[data-id-file="' + id_file + '"]').remove();
                                $.gritter.add({text: Joomla.JText._('COM_DROPFILES_JS_FILE_MOVED', 'File moved with success!')});
                                var ismoved = '<div id="dropfiles-movefile-container" style="display: none"></div>';
                                $('#mybootstrap').append(ismoved);
                            });
                        }
                    }
                }
                $(this).removeClass("ui-state-highlight");
            }
        });
    }
    catDroppable();
    if ($('#categorieslist li').length === 0) {
        $('.nested .dd-empty').html(Joomla.JText._('COM_DROPFILES_JS_PLEASE_CREATE_A_FOLDER', 'Please create a folder'));
    }
    //override Joomla.submitbutton
    var oldJoomlaSubmition = Joomla.submitbutton;
    var selectedFiles = [];
    var lastAction = '';
    var sourceCat = 0;
    Joomla.submitbutton = function ($task) {
        if ($task == 'files.copyfile' || $task == 'files.movefile') {

            if ($('#preview .file.selected').length == 0) {
                bootbox.alert(Joomla.JText._('COM_DROPFILES_JS_NO_FILES_SELETED', 'Please select file(s)'));
                return;
            }
            lastAction = $task;
            sourceCat = $('#categorieslist li.active').data('id-category');
            selectedFiles = [];
            $('#preview .file.selected').each(function (index) {
                selectedFiles.push($(this).data('id-file'));
            });
            if (lastAction == 'files.copyfile') {
                //do nothing
            } else {
                $('#preview .file.selected').css('opacity', '0.7');
            }

            var numberfiles = '<span class="dropfiles-number-files">' + $('#preview .file.selected').length + '</span>';
            var type = 'cut';
            if ($task == 'files.copyfile') {
                type = 'copy';
            } else if ($task == 'files.movefile') {
                type = 'cut';
            }
            $('.dropfiles-number-files').remove();

            $('#dropfiles-' + type).prepend(numberfiles);
        }
        else if ($task == 'files.paste') {
            if (selectedFiles.length == 0) {
                bootbox.alert(Joomla.JText._('COM_DROPFILES_JS_NO_FILES_COPIED_CUT', 'There is no copied/cut files yet'));
            }
            cat_target = $('#categorieslist li.active').data('id-category');
            if (cat_target != sourceCat) {
                countFiles = selectedFiles.length;
                iFile = 0;
                while (selectedFiles.length > 0) {
                    id_file = selectedFiles.pop();
                    $.ajax({
                        url: "index.php?option=com_dropfiles&task=" + lastAction + "&id_category=" + cat_target + '&active_category=' + sourceCat + '&id_file=' + id_file,
                        type: "POST"
                    }).done(function (data) {
                        iFile++;
                        result = jQuery.parseJSON(data);
                        if (result.response.onedrive_catkey !== undefined) {
                            checkCopyOnedrive(result.response.onedrive_catkey, result.response.location, cat_target);
                        } else {
                            if (iFile == countFiles) {
                                if (lastAction == 'files.copyfile') {
                                    $.gritter.add({text: Joomla.JText._('COM_DROPFILES_JS_FILES_COPIED', 'File(s) copied with success!')});
                                } else {
                                    $.gritter.add({text: Joomla.JText._('COM_DROPFILES_JS_FILES_MOVED', 'File(s) moved with success!')});
                                }

                                updatepreview(cat_target);
                            }
                        }
                    });
                }
            }
            $('.dropfiles-number-files').remove();
        } else if ($task == 'files.uncheck') {
            selectedFiles = [];
            $('.file').removeClass('selected');
            $('.dropfiles-btn-toolbar').find('#dropfiles-cut, #dropfiles-copy, #dropfiles-paste, #dropfiles-delete, #dropfiles-download, #dropfiles-uncheck').hide();
            $('.dropfiles-number-files').remove();
            showCategory();
        } else if ($task == 'files.delete') {
            bootbox.confirm(Joomla.JText._('COM_DROPFILES_JS_ARE_YOU_SURE_DELETE', 'Are you sure you want to delete the files you have selected') + '?', function (result) {
                if (result === true) {
                    sourceCat = $('#categorieslist li.active').data('id-category');
                    selectedFiles = [];
                    $('#preview .file.selected').each(function (index) {
                        selectedFiles.push({
                            'id_File': $(this).data('id-file'),
                            'id_CateRef': $(this).data('id-category'),
                        });
                    })
                    cat_target = $('#categorieslist li.active').data('id-category');
                    if (cat_target == sourceCat) {
                        while (selectedFiles.length > 0) {
                            selectedFile = selectedFiles.pop();
                            id_file = selectedFile.id_File;
                            id_cateRef = selectedFile.id_CateRef;
                            $.ajax({
                                url: "index.php?option=com_dropfiles&task=files.delete&id_file=" + id_file + "&id_cat=" + sourceCat + "&id_cate_ref=" + id_cateRef,
                                type: "POST"
                            }).done(function (data) {
                                result = jQuery.parseJSON(data);
                                if (result === true) {
                                    $('tr[data-id-file="' + id_file + '"]').fadeOut(500, function () {
                                        $(this).remove();
                                        $('.fileblock #fileparams').empty();
                                        updatepreview(cat_target);
                                        $.gritter.add({text: Joomla.JText._('COM_DROPFILES_JS_FILES_REMOVED', 'File(s) removed with success!')});
                                    });
                                } else {
                                    bootbox.alert(result.response);
                                }
                            });
                        }
                    }
                }
            });
            return false;
        } else if ($task == 'files.download') {
            $('#preview .file.selected').each(function (index) {
                var link = document.createElement("a");
                link.download = '';
                link.href = $(this).data('linkdownload');
                $('body').append(link);
                link.click();
                $(link).remove();
            });
        } else if($task == 'files.checkall') {
            $('.file').addClass('selected');
            $('.dropfiles-btn-toolbar').find('#dropfiles-cut, #dropfiles-copy, #dropfiles-paste, #dropfiles-delete, #dropfiles-download, #dropfiles-uncheck').show();
        }
        else {
            oldJoomlaSubmition($task);
        }

    }

    //check copy onedrive
    function checkCopyOnedrive(catid, url_respons, cat_target) {
        $.ajax({
            url: "index.php?option=com_dropfiles&task=file.oneDriveCopyRespone&cat_id=" + catid + '&url=' + url_respons,
            type: "POST"
        }).done(function (data) {
            updatepreview(cat_target);
        });
    }


    /* Init version dropbox */
    initDropboxVersion($('#dropbox_version'));
    $('#upload_button_version').on('click', function () {
        $('#upload_input_version').trigger('click');
        return false;
    });

    /* Init File import */
    if (Dropfiles.can.config) {
        $('#jao').jaofiletree({
            script: 'index.php?option=com_dropfiles&task=connector.listDir&tmpl=component',
            usecheckboxes: 'files',
            showroot: '/'
        });
    }
    $('#importFilesBtn').click(function () {
        id_category = $('input[name=id_category]').val();
        var files = '';
        $($('#jao').jaofiletree('getchecked')).each(function () {
            files += '&files[]=' + this.file;
        });
        if (files === '' || !id_category) {
            return;
        }
        $.ajax({
            url: "index.php?option=com_dropfiles&task=files.import&" + $('#categoryToken').attr('name') + "=1&id_category=" + id_category,
            type: 'GET',
            data: files
        }).done(function (data) {
            result = jQuery.parseJSON(data);
            if (result.response === true) {
                bootbox.alert(result.datas.nb + Joomla.JText._('COM_DROPFILES_JS_X_FILES_IMPORTED', ' files imported'));
                updatepreview(id_category);
            } else {
                if (typeof(result.datas) !== 'undefined' && result.datas == 'noerror') {

                } else {
                    bootbox.alert(result.response);
                }
            }
        });
        return false;
    });
    $('#selectAllImportFiles').click(function () {
        $('#filesimport input[type="checkbox"]').attr('checked', true);
    });
    $('#unselectAllImportFiles').click(function () {
        $('#filesimport input[type="checkbox"]').attr('checked', false);
    });


    function showCategory() {
        $('.fileblock').fadeOut(function () {
            $('.categoryblock').fadeIn();
        });
        $('#insertfile').fadeOut(function () {
            $('#insertcategory').fadeIn();
        });

    }

    function showFile(e) {
//        $('#singleimage').attr('src',$(e).attr('src'));
        $('.categoryblock').fadeOut(function () {
            $('.fileblock').fadeIn();
        });
        $('#insertcategory').fadeOut(function () {
            $('#insertfile').fadeIn();
        });
    }

    /**
     * Reload a category preview
     * @param id_category
     * @param id_file
     */
    function updatepreview(id_category, id_file, order, order_dir) {
        if (typeof(id_category) === "undefined" || id_category === null) {
            id_category = checkCateActive(id_category);
            if (typeof(id_category) === 'undefined') {
                $('#insertcategory').hide();
                return;
            }
            $('input[name=id_category]').val(id_category);
        } else {
            // $('#preview')
            id_category = checkCateActive(id_category);
        }
        loading('#wpreview');
        url = "index.php?option=com_dropfiles&view=files&format=raw&id_category=" + id_category;
        if (typeof(order) === 'string') {
            url = url + '&orderCol=' + order;
        }
        if (order_dir === 'asc') {
            url = url + '&orderDir=desc';
        } else if (order_dir === 'desc') {
            url = url + '&orderDir=asc';
        }

        var oldCategoryAjax = categoryAjax;
        if (oldCategoryAjax !== null) {
            oldCategoryAjax.abort();
        }
        categoryAjax = $.ajax({
            url: url,
            type: "POST"
        }).done(function (data) {
            $('#preview').contents().remove();
            $(data).hide().appendTo('#preview').fadeIn(200);
            rloading('#wpreview');
            if (selectedFiles.length == 0) {
                $('.dropfiles-btn-toolbar #dropfiles-cut').hide();
                $('.dropfiles-btn-toolbar #dropfiles-copy').hide();
                $('.dropfiles-btn-toolbar #dropfiles-paste').hide();
                $('.dropfiles-btn-toolbar #dropfiles-delete').hide();
                $('.dropfiles-btn-toolbar #dropfiles-download').hide();
                $('.dropfiles-btn-toolbar #dropfiles-uncheck').hide();
            }
            if (Dropfiles.can.edit || (Dropfiles.can.editown && Dropfiles.author === $('#categorieslist li.active').data('author'))) {
                var remote_file = (Dropfiles.addRemoteFile == 1) ? '<a href="" id="add_remote_file" class="btn btn-large btn-primary">' + Joomla.JText._('COM_DROPFILES_JS_ADD_REMOTE_FILE', 'Add remote file') + '</a> ' : '';
                $('<div id="dropbox"><span class="message">' + Joomla.JText._('COM_DROPFILES_JS_DROP_FILES_HERE', 'Drop files here to upload') + '</span><input class="hide" type="file" id="upload_input" multiple="">' + remote_file + '<span id="upload_button" class="btn btn-large btn-primary">' + Joomla.JText._('COM_DROPFILES_JS_SELECT_FILES', 'Select files') + '</span></div><div class="clr"></div>').appendTo('#preview');

                $('#add_remote_file').on('click', function (e) {
                    e.preventDefault();
                    var allowed = Dropfiles.allowedext;
                    allowed = allowed.split(',');
                    allowed.sort();
                    var allowed_select = '<select id="dropfiles-remote-type">';
                    $.each(allowed, function (i, v) {
                        allowed_select += '<option value="' + v + '">' + v + '</option>';
                    });
                    allowed_select += '</select>';

                    bootbox.dialog({
                        message: '<div class="form-horizontal dropfiles-remote-form"> ' +
                        '<div class="control-group"> ' +
                        '<label class=" control-label" for="dropfiles-remote-title">' + Joomla.JText._('COM_DROPFILES_JS_REMOTE_FILE_TITLE', 'title') + '</label> ' +
                        '<div class="controls"> ' +
                        '<input id="dropfiles-remote-title" name="dropfiles-remote-title" type="text" placeholder="' + Joomla.JText._('COM_DROPFILES_JS_REMOTE_FILE_TITLE', 'title') + '" class=""> ' +
                        '</div> ' +
                        '</div> ' +
                        '<div class="control-group"> ' +
                        '<label class="control-label" for="dropfiles-remote-url">' + Joomla.JText._('COM_DROPFILES_JS_REMOTE_FILE_REMOTE_URL', 'Remote URL') + '</label> ' +
                        '<div class="controls">' +
                        '<input id="dropfiles-remote-url" name="dropfiles-remote-url" type="text" placeholder="' + Joomla.JText._('COM_DROPFILES_JS_REMOTE_FILE_URL', 'URL') + '" class=""> ' +
                        '</div> </div>' +
                        '<div class="control-group"> ' +
                        '<label class="control-label" for="dropfiles-remote-type">' + Joomla.JText._('COM_DROPFILES_JS_REMOTE_FILE_TYPE', 'File Type') + '</label> ' +
                        '<div class="controls">' +
                        allowed_select +
                        '</div> </div>' +
                        '</div>',
                        buttons: {
                            save: {
                                "label": Joomla.JText._('COM_DROPFILES_JS_SAVE', 'Save'),
                                "className": "btn-primary",
                                "callback": function () {
                                    var category_id = $('input[name=id_category]').val();
                                    var remote_title = $('#dropfiles-remote-title');
                                    var remote_url = $('#dropfiles-remote-url');
                                    var remote_type = $('#dropfiles-remote-type');
                                    var ajax_url = "index.php?option=com_dropfiles&task=files.addremoteurl&id_category=" + category_id + '&remote_title=' + remote_title.val() + '&remote_url=' + remote_url.val() + '&remote_type=' + remote_type.val();

                                    $.ajax({
                                        url: ajax_url,
                                        type: "POST"
                                    }).done(function (data) {

                                        result = $.parseJSON(data);
                                        if (result.response === true) {
                                            updatepreview();
                                        } else {
                                            bootbox.alert(result.response);
                                        }
                                        $('.remote-dialog').remove();

                                    });
                                }
                            },
                            cancel: {
                                "label": Joomla.JText._('COM_DROPFILES_JS_CANCEL', 'Cancel'),
                                "className": "s",
                                "callback": function () {
                                    $('.remote-dialog').remove();
                                    $('.modal-backdrop').remove();
                                }
                            }
                        },
                        className: 'remote-dialog'
                    });

                    return false;
                });
            }
            $('#preview .restable').restable({
                type: 'hideCols',
                priority: {0: 'persistent', 1: 3, 2: 'persistent'},
                hideColsDefault: [4, 5]
            });

            var filehidecolumns = $.Event('dropfiles_file_hide_column_status');
            $(document).trigger(filehidecolumns);
            showhidecolumns();

            if (Dropfiles.can.edit || (Dropfiles.can.editown && Dropfiles.author === $('#categorieslist li.active').data('author'))) {
                initSortableFiles();
                $('#preview').sortable('enable');
                $('#preview').sortable('refresh');

            }
            initDeleteBtn();

            /** Show/hide right colum **/
            $('#preview .dropfiles-flip').click(function (e) {
                if ($('#rightcol').hasClass('hide')) {
                    $('#rightcol').addClass('show').removeClass('hide');
                } else {
                    $('#rightcol').addClass('hide').removeClass('show');
                }
                $(this).toggleClass('dropfiles-flip-expand');
            });
            if (Dropfiles.can.edit || (Dropfiles.can.editown && Dropfiles.author === $('#categorieslist li.active').data('author'))) {
                //initUploadBtn();
            }

            /** Init ordering **/
            $('#preview .restable thead a').click(function (e) {
                e.preventDefault();
                updatepreview(null, null, $(this).data('ordering'), $(this).data('direction'));
                if ($(this).data('direction') === 'asc') {
                    direction = 'desc';
                } else {
                    direction = 'asc';
                }
                $('#jform_params_ordering option[value="' + $(this).data('ordering') + '"]').attr('selected', 'selected').parent().animate({
                    'background-color': '#2196f3',
                    'color': '#fff',
                    'border': 'none',
                    'box-shadow': '1px 1px 12px #ccc'
                });
                $('#jform_params_orderingdir option[value="' + direction + '"]').attr('selected', 'selected').parent().animate({
                    'background-color': '#2196f3',
                    'color': '#fff',
                    'border': 'none',
                    'box-shadow': '1px 1px 12px #ccc'
                });
            });
            initFiles();


            $('#wpreview').unbind();
            //initDropbox($('#wpreview'));
            Dropfiles.uploader.assignBrowse($('#upload_button'));
            Dropfiles.uploader.assignDrop($('#wpreview'));

            theme = $('input[name=theme]').val();
            $('#themeselect .themebtn').removeClass('selected');
            $('#themeselect a[data-theme=' + theme + ']').addClass('selected');

            if (typeof(id_file) !== "undefined" && id_file !== null) {
                $('#preview .file[data-id-file=' + id_file + ']').trigger('click');
            } else {
                showCategory();
                if (typeof(order) === 'undefined') {
                    $('.fileblock #fileparams').empty();
                    loadGalleryParams();
                }
            }

            rloading('#wpreview');
            $('#mybootstrap #preview').trigger('dropfiles_preview_updated');
        });

        initDeleteBtn();
    }

    $('#wpreview .restablesearch').click(function (e) {
        e.preventDefault();
        $('.dropfiles-search-file').addClass('show').removeClass('hide');
        $('#mycategories').hide();
        $('.dropfiles-btn-toolbar').hide();
        $(this).hide();
    });

    $('.dropfiles-btn-exit-search').click(function (e) {
        e.preventDefault();
        $('.dropfiles-search-file').addClass('hide').removeClass('show');
        $('#mycategories').show();
        $('.dropfiles-iconsearch').show();
        $('.dropfiles-btn-toolbar').show();
        $('.dropfiles-filter-file').css('right', '86px');
        $('.dropfiles-search-file .dropfiles-search-file-input').val('');
        $('#dropfiles_filter_catid').val('');
        $currentcateid = $('#mycategories li.dd-item.active').attr('data-id-category');
        updatepreview($currentcateid);
    });

    $('#dropfiles_filter_catid').change(function (e) {
        e.preventDefault();
        var filter_catid = $(this).val();

        var filter_cattype = $(this).find(':selected').data('type');
        if (filter_catid) {
            var keyword = $('.dropfiles-search-file-input').val();
            searchFiles(keyword, filter_catid, filter_cattype);
        }

    });

    $(".dropfiles-search-file-input").on('keyup', function (e) {
        if (e.keyCode == 13) {
            var keyword = $(this).val();
            if (keyword) {
                searchFiles(keyword);
            }
        }
    });

    $('.dropfiles-btn-search').click(function (e) {
        e.preventDefault();
        var keyword = $('.dropfiles-search-file-input').val();
        searchFiles(keyword);
    });

    function searchFiles(keyword, filter_catid, filter_cattype, ordering, ordering_dir) {
        if (typeof(filter_catid) === "undefined" || filter_catid === null) {
            filter_catid = $('#dropfiles_filter_catid').val();
            filter_cattype = $('#dropfiles_filter_catid').find(':selected').data('type');
        }
        var url = "index.php?option=com_dropfiles&view=files&layout=search&format=raw";
        $.ajax({
            url: url,
            type: "POST",
            data: {
                "s": keyword,
                "cid": filter_catid,
                "cattype": filter_cattype,
                "orderCol": ordering,
                "orderDir": ordering_dir
            }
        }).done(function (data) {

            $('#preview').html($(data));

            $('#preview .restable').restable({
                type: 'hideCols',
                priority: {0: 'persistent', 1: 3, 2: 'persistent'},
                hideColsDefault: [4, 5]
            });

            $('#preview').sortable('refresh');
            showhidecolumns();
            initDeleteBtn();

            $('#preview .dropfiles-flip').click(function (e) {
                if ($('#rightcol').hasClass('hide')) {
                    $('#rightcol').addClass('show').removeClass('hide');
                } else {
                    $('#rightcol').addClass('hide').removeClass('show');
                }
            });
            /** Init ordering **/
            $('#preview .restable thead a').click(function (e) {
                e.preventDefault();
                filter_catid = $('#dropfiles_filter_catid').val();
                if (!filter_catid) {
                    filter_catid = '';
                }
                searchFiles(keyword, filter_catid, filter_cattype, $(this).data('ordering'), $(this).data('direction'));

                if ($(this).data('direction') === 'asc') {
                    direction = 'desc';
                } else {
                    direction = 'asc';
                }
                $('#jform_params_ordering option[value="' + $(this).data('ordering') + '"]').attr('selected', 'selected').parent().animate({
                    'background-color': '#2196f3',
                    'color': '#fff',
                    'border': 'none',
                    'box-shadow': '1px 1px 12px #ccc'
                });
                $('#jform_params_orderingdir option[value="' + direction + '"]').attr('selected', 'selected').parent().animate({
                    'background-color': '#2196f3',
                    'color': '#fff',
                    'border': 'none',
                    'box-shadow': '1px 1px 12px #ccc'
                });

            });


            //initUploadBtn();

            initFiles();


            $('#wpreview').unbind();
            //initDropbox($('#wpreview'));
            Dropfiles.uploader.assignBrowse($('#upload_button'));
            Dropfiles.uploader.assignDrop($('#wpreview'));

            theme = $('input[name=theme]').val();
            $('#themeselect .themebtn').removeClass('selected');
            $('#themeselect a[data-theme=' + theme + ']').addClass('selected');

            if (typeof(id_file) !== "undefined" && id_file !== null) {
                $('#preview .file[data-id-file=' + id_file + ']').trigger('click');
            } else {
                showCategory();
                if (typeof(order) === 'undefined') {
                    $('.fileblock #fileparams').empty();
                    loadGalleryParams();
                }
            }

            rloading('#wpreview');
        });

        initDeleteBtn();
    }

    /** Init files **/
    function initFiles() {
        $(document).unbind('click.window').bind('click.window', function (e) {

            if ($(e.target).is('#rightcol') ||
                $(e.target).parents('#rightcol').length > 0 ||
                $(e.target).parents('#rightcol').length > 0 ||
                $(e.target).is('.modal-backdrop') ||
                $(e.target).parents('.bootbox.modal').length > 0 ||
                $(e.target).parents('.mce-container').length > 0 ||
                $(e.target).parents('.tagit-autocomplete').length > 0 ||
                $(e.target).parents('#toolbar-copy').length > 0 ||
                $(e.target).parents('#toolbar-scissors').length > 0 ||
                $(e.target).parents('.ui-datepicker-header').length > 0 ||
                $(e.target).parents('.calendar').length > 0 ||
                $(e.target).parents('.dropfiles-btn-toolbar').length > 0
            ) {
                return;
            }
            $('.fileblock #fileparams').empty();
            $('#preview .file').removeClass('selected');
            $('#preview .file').removeClass('first');
            $('#preview .file').removeClass('second');
            $('.dropfiles-btn-toolbar #dropfiles-cut').hide();
            $('.dropfiles-btn-toolbar #dropfiles-copy').hide();
            $('.dropfiles-btn-toolbar #dropfiles-paste').hide();
            $('.dropfiles-btn-toolbar #dropfiles-delete').hide();
            $('.dropfiles-btn-toolbar #dropfiles-download').hide();
            $('.dropfiles-btn-toolbar #dropfiles-uncheck').hide();
            showCategory();
        });

        $('#preview .file').unbind('click').click(function (e) {
            iselected = $(this).find('tr.selected').length;

            //Allow multiselect
            if (!e.ctrlKey && !ctrlDown && !e.shiftKey) {
                $('#preview .file.first').removeClass('first');
                $('#preview .file.second').removeClass('second');
                $(this).addClass('first');
                $('#preview .file.selected').removeClass('selected');
            } else if(e.shiftKey) {
                if($('#preview .file.first').length == 0) {
                    $(this).addClass('first');
                } else {
                    $('#preview .file.second').removeClass('second');
                    $(this).addClass('second');
                    var index1, index2;
                    $('#preview .file').each(function(index, elm) {
                        if ($(elm).hasClass('first')) {
                            index1 = index;
                        }
                        if ($(elm).hasClass('second')) {
                            index2 = index;
                        }
                    });
                    if (index1 < index2) {
                        $('#preview .file').each(function(index, elm) {
                            if (index >= index1 && index <= index2) {
                                $(elm).addClass('selected');
                            }
                        });
                    } else {
                        $('#preview .file').each(function(index, elm) {
                            if (index >= index2 && index <= index1) {
                                $(elm).addClass('selected');
                            }
                        });
                    }
                }
            }
            if (e.ctrlKey) {
                $('#preview .file.ctrl').removeClass('ctrl');
                $(this).addClass('ctrl');
                var indexctrl, indexcurrent;
                if($('#preview .file.first').length == 0) {
                    $(this).addClass('first');
                }
                $('#preview .file').each(function(index, elm) {
                    if ($(elm).hasClass('first')) {
                        indexctrl = index;
                    }
                    if ($(elm).hasClass('ctrl')) {
                        indexcurrent = index;
                    }
                });
                $('#preview .file').each(function(index, elm) {
                    if (index == indexcurrent && indexcurrent < indexctrl) {
                        $('#preview .file.first').removeClass('first');
                        $(elm).addClass('first');
                    }
                });

            }
            if (iselected === 0) {
                $(this).addClass('selected');
            }

            if ($('#preview .file.selected').length == 1) {
                if (Dropfiles.can.edit || (Dropfiles.can.editown && Dropfiles.author === $('#categorieslist li.active').data('author'))) {
                    loadFileParams();
                    loadVersions();
                }
                showFile(this);
                $('.dropfiles-btn-toolbar #dropfiles-cut').show();
                $('.dropfiles-btn-toolbar #dropfiles-copy').show();
                $('.dropfiles-btn-toolbar #dropfiles-paste').show();
                $('.dropfiles-btn-toolbar #dropfiles-delete').show();
                $('.dropfiles-btn-toolbar #dropfiles-download').show();
                $('.dropfiles-btn-toolbar #dropfiles-uncheck').show();
            } else {
                showCategory();
            }
            e.stopPropagation();
        });
    }

    $(window).resize(function () {
        hideColumns();
    });

    //hide columns base on window size
    function hideColumns() {

        var w = $(window).width();
        if (w <= 1600 && w > 1440) {
            $('input[name="restable-toggle-cols"]').prop('checked', true);
            $('#restable-toggle-col-6-0,#restable-toggle-col-5-0').prop('checked', false);
        } else if (w <= 1440 && w > 1200) {
            $('input[name="restable-toggle-cols"]').prop('checked', true);
            $('#restable-toggle-col-6-0,#restable-toggle-col-5-0,#restable-toggle-col-4-0').prop('checked', false);
        } else if (w <= 1200 && w > 1024) {
            $('input[name="restable-toggle-cols"]').prop('checked', true);
            $('#restable-toggle-col-6-0,#restable-toggle-col-5-0,#restable-toggle-col-4-0,#restable-toggle-col-3-0').prop('checked', false);
        } else if (w <= 1024) {
            $('input[name="restable-toggle-cols"]').prop('checked', true);
            $('#restable-toggle-col-6-0,#restable-toggle-col-5-0,#restable-toggle-col-4-0,#restable-toggle-col-3-0,#restable-toggle-col-2-0').prop('checked', false);
        }
    }

    //show/hide columns base on cookie
    function showhidecolumns() {
        if (!localStorage.getItem('dropfilesFileColumnState')) {
            hideColumns();
            return;
        } else {
            $('.restable thead th').hide();
            $('.restable tbody td').hide();
            var colList = JSON.parse(localStorage.getItem('dropfilesFileColumnState'));
            $.each($('input[name="restable-toggle-cols"]'), function () {
                $(this).prop('checked', false);
            });
            $.each(colList, function (index, fieldset) {
                if (parseInt(fieldset.state) == 1) {
                    $('#' + fieldset.id).prop('checked', true);
                }
            });
            $.each($('input[name="restable-toggle-cols"]'), function () {
                if($(this).is(':checked')) {
                    var col = parseInt($(this).data('col')) + 1;
                }
                if (col) {
                    $('.restable thead th:nth-child(' + col + ')').show();
                    $('.restable tbody td:nth-child(' + col + ')').show();
                }
            });
        }
    }

    function setcookie_showcolumns() {
        var column_show = [];
        $('input[name="restable-toggle-cols"]').each(function (i, v) {
            if ($(v).is(':checked')) {
                column_show.push($(v).attr('id'));
            }
        });

        var url = "index.php?option=com_dropfiles&task=files.showcolumn";
        $.ajax({
            url: url,
            type: "POST",
            data: {
                column_show: column_show
            }
        }).done(function (data) {

        });
    }

    function initDeleteBtn() {
        $('.actions .trash').unbind('click').click(function (e) {
            that = this;
            bootbox.confirm(Joomla.JText._('COM_DROPFILES_JS_ARE_YOU_SURE', 'Are you sure') + '?', function (result) {
                if (result === true) {
                    //Delete file
                    id_file = $(that).parents('.file').data('id-file');
                    id_category = $('input[name=id_category]').val();
                    $.ajax({
                        url: "index.php?option=com_dropfiles&task=files.delete&id_file=" + id_file + "&id_cat=" + id_category,
                        type: "POST"
                    }).done(function (data) {
                        result = jQuery.parseJSON(data);
                        if (result === true) {
                            $(that).parents('.file').fadeOut(500, function () {
                                $(this).remove();
                            });
                        } else {
                            bootbox.alert(result.response);
                        }
                    });
                }
            });
            return false;
        });
    }

    function loadGalleryParams() {
        if (!Dropfiles.can.edit && !(Dropfiles.can.editown && Dropfiles.author === $('#categorieslist li.active').data('author'))) {
            return;
        }
        id_category = $('input[name=id_category]').val();

        if (!id_category) {
            return;
        }

        showCategory();
        loading('#galleryparams');

        $.ajax({
            url: "index.php?option=com_dropfiles&task=category.edit&layout=form&id=" + id_category
        }).done(function (data) {
            $('#galleryparams').html(data);
            $('.minicolors').minicolors({
                position: 'top right',
                theme: 'bootstrap'
            });
            if ($('#galleryparams .field-user-wrapper').length > 0) {
                if ( $.isFunction($.fn.fieldUser) ) {
                    $('#galleryparams .field-user-wrapper').fieldUser();
                }
                $('#galleryparams .user-clear').click(function (e) {
                    e.preventDefault();
                    $('#jform_params_canview').val('');
                    $('#jform_params_canview_id').val('');
                })
            }
            $('#jform_params_usergroup').chosen();
            if (Dropfiles.categoryrestriction == 'accesslevel') {
                $('#jform_params_usergroup').parent().hide();
                $('#jform_params_usergroup-lbl').hide();
            } else {
                $('#jform_access').parent().hide();
                $('#jform_access-lbl').hide();
            }
            if (Dropfiles.selected.access) {
                $('#jform_access').val(Dropfiles.selected.access);
            }
            if (Dropfiles.selected.ordering) {
                $('#jform_params_ordering').val(Dropfiles.selected.ordering);
            }
            if (Dropfiles.selected.orderingdir) {
                $('#jform_params_orderingdir').val(Dropfiles.selected.orderingdir);
            }
            if (Dropfiles.selected.usergroup) {
                Dropfiles.selected.usergroup.forEach(function(item, index) {
                    $('#jform_params_usergroup option[value="' + item + '"]').attr('selected', "selected");
                });

                $('#jform_params_usergroup').trigger("liszt:updated");
            }

            // Select user for category owner, single user access
            $("#userModal_jform_created_user_id").on('shown.bs.modal', function(){
                console.log("show", $('#userModal_jform_created_user_id iframe').length);
                // Manually attach iframe to modal body
                if ($('#userModal_jform_created_user_id iframe').length == 0) {
                    $('#userModal_jform_created_user_id .modal-body').append($('#userModal_jform_created_user_id').attr('data-iframe')) ;
                }
            });
            $("#userModal_jform_params_canview").on('shown.bs.modal', function(){
                // Manually attach iframe to modal body
                if ($('#userModal_jform_params_canview iframe').length == 0) {
                    $('#userModal_jform_params_canview .modal-body').append($('#userModal_jform_params_canview').attr('data-iframe')) ;
                }
            });

            if (typeof($.fn.popover) != "undefined") {
                $('.hasPopover').popover({
                    trigger: 'hover',
                    placement: 'top'
                });
            }

            /*
             * auto save params when user foget save button click
             */
            autoSaveParams();
            $('#galleryparams .dropfilesparams button[type="submit"]').click(function (e) {
                e.preventDefault();
                id_category = $('input[name=id_category]').val();

                if (!id_category) {
                    return;
                }
                $.ajax({
                    url: "index.php?option=com_dropfiles&task=category.setparams&id=" + id_category,
                    type: "POST",
                    data: $('.dropfilesparams').serialize()
                }).done(function (data) {

                    result = jQuery.parseJSON(data);
                    if (result.response === true) {
                        $.gritter.add({text: Joomla.JText._('COM_DROPFILES_JS_CATEGORY_SAVED', 'Category config saved with success!')});
                        updatepreview();
                    } else {
                        bootbox.alert(result.response);
                    }

                });
                return false;
            });
            var fieldState = $.Event('dropfiles_field_settings_status');
            $(document).trigger(fieldState);
            mainSettingsToggle();
            var event = $.Event('dropfiles_category_param_loaded');
            $(document).trigger(event);
            rloading('#galleryparams');
        });
    }

    function autoSaveParams() {
        $.ajax({
            url: "index.php?option=com_dropfiles&task=category.setparams&id=" + id_category,
            type: "POST",
            data: $('.dropfilesparams').serialize()
        }).done(function (data) {
            if (data) {
                try {
                    result = jQuery.parseJSON(data);
                    if (result.response !== true) {
                        bootbox.alert(result.response);
                    }
                } catch (err) {
                }
            }
        });
    }

    function initThemeBtn() {
        $('#themeselect .themebtn').on('click', function (e) {
            e.preventDefault();
            id_category = $('input[name=id_category]').val();
            if (!id_category) {
                return;
            }
            theme = $(this).data('theme');
            Dropfiles.selected.access = $('#jform_access').val();
            Dropfiles.selected.ordering = $('#jform_params_ordering').val();
            Dropfiles.selected.orderingdir = $('#jform_params_orderingdir').val();
            Dropfiles.selected.usergroup = $('#jform_params_usergroup').val();
            $.ajax({
                url: 'index.php?option=com_dropfiles&task=config.setTheme&theme=' + theme + '&id=' + id_category,
                type: 'POST'
            }).done(function (data) {
                $('.themesblock #themeselect .themebtn').removeClass('selected');
                $('.themesblock #themeselect a[data-theme=' + theme + ']').addClass('selected');
                result = jQuery.parseJSON(data);
                if (result === true) {
                    loadGalleryParams();
                }
            });
        });
    }

    function loadFileParams() {
        id_category = $('input[name=id_category]').val();
        if (!id_category) {
            return;
        }
        is_remoteurl = jQuery('.file.selected').hasClass('is-remote-url');
        var linkdownload = jQuery('.file.selected').data('friendlylinkdownload');
        id_file = jQuery('.file.selected').data('id-file');
        catid_file = jQuery('.file.selected').data('id-category');
        if (id_file && jQuery.isNumeric(id_file)) {
            if (catid_file.toString() !== id_category) {
                $('#fileversion').hide();
                var txt1 = "<p class='original-file-info'>" + Joomla.JText._('COM_DROPFILES_MULTI_CATEGORY_FILE', 'This file is listed in several categories, settings are available in the original version of the file') + "</p>";
                var btn = "<a class='button button-primary edit-original-file'>" + Joomla.JText._('COM_DROPFILES_MULTI_CATEGORY_EDIT_ORIGINAL_FILE', 'EDIT ORIGINAL FILE') + "</a>";
                $('#fileparams').html('<div class="original-file-params">'+ txt1 + btn +'</div>');
                $('#fileparams .edit-original-file').click(function (e) {
                    Dropfiles.catRefTofileId = id_file;
                    $('li.dd-item.dd3-item[data-id-category="' + catid_file + '"] >div.dd-content').click();
                    if ($('.dropfiles-search-file.show')) {
                        $('#dropfiles_filter_catid').val(catid_file.toString());
                    }
                });
                return true;
            }
        }
        Dropfiles.catRefTofileId = false;
        $('#fileversion').show();
        loading('#rightcol');

        $.ajax({
            url: "index.php?option=com_dropfiles&task=file.edit&layout=form&id=" + id_file + "&catid=" + catid_file
        }).done(function (data) {
            // fix tinymce toolbar wrong position in the file description
            if (typeof tinymce !== 'undefined' && typeof tinymce.get('jform_description') !== 'undefined') {
                tinymce.get('jform_description').destroy();
            }
            $('#fileparams').html(data);
            if ($('#fileparams .field-multiple-user-wrapper').length > 0) {
                if ( $.isFunction($.fn.fieldMultipleUser) ) {
                    $('#fileparams .field-multiple-user-wrapper').fieldMultipleUser();
                }
                $('#fileparams .user-clear').click(function (e) {
                    e.preventDefault();
                    $('#jform_canview').val('');
                    $('#jform_canview_id').val('');
                })

                $("#userModal_jform_canview").on('shown.bs.modal', function(){
                    // Manually attach iframe to modal body
                    if ($('#userModal_jform_canview iframe').length == 0) {
                        $('#userModal_jform_canview .modal-body').append($('#userModal_jform_canview').attr('data-iframe')) ;
                    }
                });
            }
            if (is_remoteurl) {
                $('.dropfilesparams').find('.dropfiles-hide').removeClass('dropfiles-hide');
            }
            // Set download link to file_direct_link
            $('#jform_file_direct_link').val(linkdownload);
            $('#jform_file_direct_link + .copy-btn').unbind('click').on('click', function(e) {
              var linkcopy = $('#jform_file_direct_link').val();
              var inputlink = document.createElement("input");
              inputlink.setAttribute("value", linkcopy);
              document.body.appendChild(inputlink);
              inputlink.select();
              document.execCommand("copy");
              document.body.removeChild(inputlink);
              $.gritter.add({text: Joomla.JText._('COM_DROPFILES_JS_LINK_COPIED', 'File URL copied to clipboard!')});
            });
            $('#jform_params_usergroup').chosen();
            if (typeof(JoomlaCalendar) != "undefined") { //joomla version>= 3.7
                JoomlaCalendar.init($(".field-calendar")[0]);
                JoomlaCalendar.init($(".field-calendar")[1]);
                JoomlaCalendar.init($(".field-calendar")[2]);
                JoomlaCalendar.init($(".field-calendar")[3]);
                if (typeof($.fieldMedia) != "undefined") {
                    $('.field-media-wrapper').fieldMedia();
                }
            } else {
                Calendar.setup({
                    // Id of the input field
                    inputField: "jform_created_time",
                    // Format of the input field
                    ifFormat: "%Y-%m-%d %H:%M:%S",
                    // Trigger for the calendar (button ID)
                    button: "jform_created_time_img",
                    // Alignment (defaults to "Bl")
                    align: "Tl",
                    cache: true,
                    singleClick: true
                });

                Calendar.setup({
                    // Id of the input field
                    inputField: "jform_publish",
                    // Format of the input field
                    ifFormat: "%Y-%m-%d %H:%M:%S",
                    // Trigger for the calendar (button ID)
                    button: "jform_publish_img",
                    // Alignment (defaults to "Bl")
                    align: "Tl",
                    cache: true,
                    singleClick: true
                });

                Calendar.setup({
                    // Id of the input field
                    inputField: "jform_publish_down",
                    // Format of the input field
                    ifFormat: "%Y-%m-%d %H:%M:%S",
                    // Trigger for the calendar (button ID)
                    button: "jform_publish_down_img",
                    // Alignment (defaults to "Bl")
                    align: "Tl",
                    cache: true,
                    singleClick: true
                });
            }

            // Turn radios into btn-group
            $('.radio.btn-group label').addClass('btn');
            $('.btn-group label:not(.active)').click(function () {
                var label = $(this);
                var input = $('#' + label.attr('for'));

                if (!input.prop('checked')) {
                    label.closest('.btn-group').find('label').removeClass('active btn-success btn-danger btn-primary');
                    input.prop('checked', true);
                    if (input.val() == 0) {
                        label.addClass('active btn-danger');
                    } else {
                        label.addClass('active btn-success');
                    }

                    input.trigger('change');
                }
            });
            $('.btn-group input[checked=checked]').each(function () {
                if ($(this).val() == 0) {
                    $('label[for=' + $(this).attr('id') + ']').addClass('active btn-danger');
                } else {
                    $('label[for=' + $(this).attr('id') + ']').addClass('active btn-success');
                }

            });

            $(".googleblock").find('#jform_created_time').parent().parent().hide();
            $(".googleblock").find('label[for="jform_created_time"]').hide();

            $(".googleblock").find('#jform_modified_time').parent().parent().hide();
            $(".googleblock").find('label[for="jform_modified_time"]').hide();


            // SqueezeBox.initialize(SqueezeBox_options);
            if (typeof(SqueezeBox) !== 'undefined') {
                SqueezeBox.assign($('.paraminput a.modal').get(), {
                    parse: 'rel'
                });
            }
            ;

            $('#fileparams .dropfilesparams button[type="submit"]').click(function (e) {
                e.preventDefault();
                id_file = jQuery('.file.selected').data('id-file');
//                type = jQuery('.file div.selected').parent().data('type');
                id_category = $('input[name=id_category]').val();
                if (!id_category) {
                    return;
                }
                $.ajax({
                    url: "index.php?option=com_dropfiles&task=file.save&id=" + id_file + "&catid=" + id_category,
                    type: "POST",
                    data: $('.dropfilesparams').serialize()
                }).done(function (data) {
                    result = jQuery.parseJSON(data);
                    if (result.response === true) {
                        $.gritter.add({text: Joomla.JText._('COM_DROPFILES_JS_FILES_SAVED', 'File config saved with success!')});
                        //loadFileParams();
                    } else {
                        bootbox.alert(result.response);
                    }
                    loadFileParams();
                    updatepreview(null, id_file);
                });
                return false;
            });
            $(".chosen-select").chosen({
                allow_single_deselect: true,
                width: '100%',
                no_results: "No results"
            });
            var itemState = $.Event('dropfiles_item_settings_status');
            $(document).trigger(itemState);
            if($('.jform_custom_icon .button-clear').length || $('.jform_custom_icon .input-prepend > a:not(.modal)').length) {
                $('.jform_custom_icon .button-clear').text(Joomla.JText._('COM_DROPFILES_JS_CLEAR', 'Clear'));
                $('.jform_custom_icon .input-prepend > a:not(.modal)').text(Joomla.JText._('COM_DROPFILES_JS_CLEAR', 'Clear'));
            }
            rloading('#rightcol');
        });
    }

    function loadVersions() {
        id_category = $('input[name=id_category]').val();
        if (!id_category) {
            return;
        }
        id_file = jQuery('.file.selected').data('id-file');
        loading('#fileversion');
        $.ajax({
            url: "index.php?option=com_dropfiles&view=files&layout=versions&format=raw&id_file=" + id_file + "&id_category=" + id_category
        }).done(function (data) {
            $('#versions_content').html(data);
            $('#versions_content a.trash').click(function () {
                that = this;
                bootbox.confirm(Joomla.JText._('COM_DROPFILES_JS_ARE_YOU_SURE_DELETE_FILE_VERSION', 'Are you sure you want to definitively remove this file version') + '?', function (result) {
                    if (result === true) {
                        id = $(that).data('id');
                        vid = $(that).data('vid');
                        $.ajax({
                            url: "index.php?option=com_dropfiles&task=file.deleteVersion&vid=" + vid + "&id=" + id + "&id_file=" + id_file + "&catid=" + id_category,
                            type: "POST"
                        }).done(function (data) {
                            result = jQuery.parseJSON(data);
                            if (result.response === true) {
                                $(that).parents('tr').remove();
                            } else {
                                bootbox.alert(result.response);
                            }
                        });
                    }
                });
                return false;
            });

            $('#versions_content a.restore').click(function (e) {
                e.preventDefault();
                that = this;
                file_ext = jQuery('.file.selected .txt').text();
                file_title = jQuery('.file.selected .title').text();
                bootbox.confirm(Joomla.JText._('COM_DROPFILES_JS_ARE_YOU_SURE_RESTORE_FILE', 'Are you sure you want to restore the file: ') + file_title + '?', function (result) {
                    if (result === true) {
                        vid = $(that).data('vid');
                        id = $(that).data('id');
                        catid = $(that).data('catid');
                        $.ajax({
                            url: "index.php?option=com_dropfiles&task=file.restoreVersion&id=" + id + "&vid=" + vid + "&id_file=" + id_file + "&catid=" + id_category,
                            type: "POST"
                        }).done(function (data) {
                            result = jQuery.parseJSON(data);
                            if (result.response === true) {
                                $(that).parents('tr').remove();
                                id_file = jQuery('.file.selected').data('id-file');
                                updatepreview(null, id_file);

                            } else {
                                bootbox.alert(result.response);
                            }

                        });
                    }
                });

                return false;
            });

            rloading('#fileversion');
        });
    }

    function initUploadBtn() {

        $('#upload_button').on('click', function (e) {
            e.preventDefault();

            $('#upload_input').trigger('click');
            return false;
        });
    }

    /**
     * Click on new category btn
     */
    $('#newcategory a:not(.dropdown-toggle)').on('click', function (e) {
        e.preventDefault();
        if ($(this).hasClass('googleCat')) {
            type = 'googledrive';
        } else if ($(this).hasClass('dropboxcat')) {
            type = 'dropbox';
        } else if ($(this).hasClass('onedrivecat')) {
            type = 'onedrive';
        } else if ($(this).hasClass('onedrivebusinesscat')) {
            type = 'onedrivebusiness';
        } else {
            type = 'joomla';
        }
        $.ajax({
            url: "index.php?option=com_dropfiles&task=category.addCategory&type=" + type,
            type: 'POST',
            data: $('#categoryToken').attr('name') + '=1'
        }).done(function (data) {
            try {
                result = jQuery.parseJSON(data);
            } catch (err) {
                bootbox.alert('<div>' + data + '</div>');
            }
            if (result.response === true) {
                var icon = '<i class="zmdi zmdi-folder dropfiles-folder"></i>';
                if (type === 'googledrive') {
                    icon = '<i class="google-drive-icon-white"></i> ';
                } else if (type == 'dropbox') {
                    icon = '<i class="dropbox-icon-white"></i> ';
                } else if (type == 'onedrive') {
                    icon = '<i class="onedrive-icon-white"></i> ';
                } else if (type == 'onedrivebusiness') {
                    icon = '<i class="onedrive-business-icon-white"></i> ';
                }

                link = '' +
                    '<li class="dd-item dd3-item" data-id-category="' + result.datas.id_category + '" data-author="' + Dropfiles.author + '">' +
                    '<div class="dd-handle dd3-handle">' + icon + '</div>' +
                    '<div class="dd-content dd3-content dd-handle">' +
                    '<a class="edit"><i class="icon-edit"></i></a>' +
                    '<a class="trash"><i class="icon-trash"></i></a>' +
                    '<a href="" class="t">' +
                    '<span class="title">' + result.datas.name + '</span>' +
                    '</a>' +
                    '</div>';
                $(link).appendTo('#categorieslist');
                initMenu();
                catDroppable();
                $('#mycategories #categorieslist li[data-id-category=' + result.datas.id_category + '] .dd-content').click();
                $('#insertcategory').show();
                $('#categoryToken + .dd-empty').remove();
                $.gritter.add({text: Joomla.JText._('COM_DROPFILES_JS_CATEGORY_CREATED', 'Category created with success!')});
            } else {
                bootbox.alert(result.response);
            }
        });
    });
    $('.btn-index-google').on('click', function (e) {
        e.preventDefault();
        var $this = $(this);
        $this.attr('disabled', true);
        $this.html('Indexing Google Drive');
        $.ajax({
            url: $(this).attr('href'),
            type: 'POST',
            data: {}
        }).done(function (data) {
            $this.attr('disabled', false);
            $this.html('Index Google Drive');
        })
    });

    function toMB(mb) {
        return mb * 1024 * 1024;
    }

    var allowedExt = Dropfiles.allowedext;
    if (typeof(allowedExt) === 'undefined') {
        allowedExt = '';
    }
    allowedExt = allowedExt.split(',');
    allowedExt.sort();
    // Init status functions
    Dropfiles.progressAdd = function (prgId, fileName, fileCatId) {
        var progressBar = '<div class="dropfiles_progress_block" data-id="' + prgId + '" data-cat-id="' + fileCatId + '">'
            + '<div class="dropfiles_progress_fileinfo">'
            + '<span class="dropfiles_progress_filename">' + fileName + '</span>'
            + '<span class="dropfiles_progress_cancel"></span>'
            + '<span class="dropfiles_progress_pause"></span>'
            + '</div>'
            + '<div class="dropfiles_process_full" style="display: block;">'
            + '<div class="dropfiles_process_run" id="' + prgId + '" data-w="0" style="width: 0%;"></div>'
            + '</div>'
            + '</div>';
        $('#preview table.restable').after(progressBar);
        $('#preview').find('.dropfiles_progress_block[data-id="' + prgId + '"] .dropfiles_progress_cancel').on('click', Dropfiles.progressInitCancel);
        $('#preview').find('.dropfiles_progress_block[data-id="' + prgId + '"] .dropfiles_progress_pause').on('click', Dropfiles.progressInitPause);

        var file = Dropfiles.uploader.getFromUniqueIdentifier(prgId);
        Dropfiles.uploader.updateQuery({
            id_category: fileCatId,
        });

        for (var num = 1; num <= Dropfiles.uploader.getOpt('simultaneousUploads'); num++) {
            if (typeof(file.chunks[num - 1]) !== 'undefined') {
                if (file.chunks[num - 1].status() === 'pending' && file.chunks[num - 1].preprocessState === 0) {
                    file.chunks[num - 1].send();
                }
            }
        }
    }
    Dropfiles.progressInitCancel = function (e) {
        e.stopPropagation();
        var $this = $(e.target);
        var progress = $this.parent().parent();
        var fileId = progress.data('id');
        var fileCatId = progress.data('cat-id');
        if (typeof(fileId) !== 'undefined') {
            // Bind
            var file = Dropfiles.uploader.getFromUniqueIdentifier(fileId);
            if (file !== false) {
                file.cancel();
                Dropfiles.progressUpdate(fileId, '0%');
            }
            progress.fadeOut('normal', function () {
                $(this).remove();
                // wpfd_status.close();
            });

            // todo: modify this to pause all uploading files
            if (Dropfiles.uploader.files.length === 0) {
                $('.dropfiles_progress_pause.all').fadeOut('normal', function () {
                    $(this).remove();
                });
            }

            $.ajax({
                url: 'index.php?option=com_dropfiles&task=files.upload',
                method: 'POST',
                data: {
                    id_category: fileCatId,
                    deleteChunks: fileId
                },
                success: function (res, stt) {
                    if (res.response === true) {

                    }
                }
            });
        }
    }
    Dropfiles.progressInitPause = function (e) {
        e.stopPropagation();
        var $this = $(e.target);
        var progress = $this.parent().parent();
        var fileId = progress.data('id');
        if (fileId !== undefined) {
            // Bind
            var file = Dropfiles.uploader.getFromUniqueIdentifier(fileId);
            if (file !== false && file.isUploading()) {
                file.abort();
                file.pause(true); // This is very important or paused file will upload after this done
                // Init play button
                $this.addClass('paused');
                $this.text('');
                $this.css('color', 'green');
                Dropfiles.progressUpdate(fileId, Math.floor(file.progress() * 100) + '%');
                $this.unbind('click').on('click', Dropfiles.progressInitContinue);
            }

        }
    }
    Dropfiles.progressInitContinue = function (e) {
        e.stopPropagation();
        var $this = $(e.target);
        var progress = $this.parent().parent();
        var fileId = progress.data('id');
        if (fileId !== undefined) {
            // Bind
            var file = Dropfiles.uploader.getFromUniqueIdentifier(fileId);
            if (file !== false && !file.isUploading()) {
                for (var num = 1; num <= Dropfiles.uploader.getOpt('simultaneousUploads'); num++) {
                    for (var i = 0; i < file.chunks.length; i++) {
                        if (file.chunks[i].status() === 'pending' && file.chunks[i].preprocessState === 0) {
                            file.chunks[i].send();
                            file.pause(false); // This is very important or file will not start after paused!
                            break;
                        }
                    }
                }

                // Init pause button
                $this.removeClass('paused');
                $this.text('');
                $this.css('color', '#ff8000');
                $this.unbind('click').on('click', Dropfiles.progressInitPause);
            }
        }
    }
    Dropfiles.progressUpdate = function (prgId, value) {
        $('#preview').find('#' + prgId).css('width', value);
    }
    Dropfiles.progressDone = function (prgId) {
        var progress = jQuery('.dropfiles_progress_block[data-id="' + prgId + '"]');
        progress.find('.dropfiles_progress_cancel').addClass('uploadDone').unbind('click');
        progress.find('.dropfiles_progress_pause').css('visibility', 'hidden');
        progress.find('.dropfiles_progress_full').remove();
        setTimeout(function () {
            jQuery('.dropfiles_progress_block[data-id="' + prgId + '"]').fadeIn(300).hide(300, function () {
                jQuery(this).remove();
            });
        }, 1000);
    }
    // Init the uploader
    Dropfiles.uploader = new Resumable({
        target: 'index.php?option=com_dropfiles&task=files.upload',
        query: {
            id_category: $('input[name=id_category]').val()
        },
        fileParameterName: 'file_upload',
        simultaneousUploads: 2,
        maxChunkRetries: 1,
        maxFileSize: toMB(Dropfiles.maxfilesize),
        maxFileSizeErrorCallback: function (file) {
            bootbox.alert(file.name + ' ' + Joomla.JText._('COM_DROPFILES_JS_FILE_TOO_LARGE', 'is too large') + '!');
        },
        chunkSize: Dropfiles.chunkSize,
        forceChunkSize: true,
        fileType: allowedExt,
        fileTypeErrorCallback: function (file) {
            bootbox.alert(file.name + ' cannot upload!<br/><br/>' + Joomla.JText._('COM_DROPFILES_CTRL_FILES_WRONG_FILE_EXTENSION'));
        },
        generateUniqueIdentifier: function (file, event) {
            var relativePath = file.webkitRelativePath || file.fileName || file.name;
            var size = file.size;
            var prefix = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
            return (prefix + size + '-' + relativePath.replace(/[^0-9a-zA-Z_-]/img, ''));
        }
    });

    if (!Dropfiles.uploader.support) {
        bootbox.alert(Joomla.JText._('COM_DROPFILES_JS_BROWSER_NOT_SUPPORT_HTML5', 'Your browser does not support HTML5 file uploads!'));
    }

    Dropfiles.uploader.on('filesAdded', function (files) {
        var categoryId = $('input[name=id_category]').val();
        files.forEach(function (file) {
            Dropfiles.progressAdd(file.uniqueIdentifier, file.fileName, categoryId);
        });
    });

    Dropfiles.uploader.on('fileProgress', function (file) {
        // $('.dropfiles_process_run#' + file.uniqueIdentifier).width(Math.floor(file.progress() * 100) + '%');
        Dropfiles.progressUpdate(file.uniqueIdentifier, Math.floor(file.progress() * 100) + '%');
    });

    Dropfiles.uploader.on('fileSuccess', function (file, res) {
        // $('.dropfiles_process_run#' + file.uniqueIdentifier).parent('.dropfiles_process_full').remove();
        Dropfiles.progressDone(file.uniqueIdentifier);
        var response = JSON.parse(res);
        if (typeof response.datas.id !== 'undefined') {
            $.ajax({
                url: 'index.php?option=com_dropfiles&task=files.ftsIndex',
                method: 'POST',
                data: {id: response.datas.id},
            });
        }

        if (typeof(response) === 'string') {
          $.gritter.add('<div>' + response + '</div>');
          return false;
        }

        if (response.response !== true) {
          $.gritter.add(response.response);
          return false;
        }

        $.gritter.add({text: file.fileName + ' ' + Joomla.JText._('COM_DROPFILES_CTRL_FILES_UPLOAD_FILE_SUCCESS', 'uploaded successfully!')});
    });

    Dropfiles.uploader.on('fileError', function (file, msg) {
        $.gritter.add({
            text: file.fileName + ' error while uploading!',
            class_name: 'error-msg'
        });
    });

    Dropfiles.uploader.on('complete', function () {
        $('#preview .progress').delay(300).fadeIn(300).hide(300, function () {
            $(this).remove();
        });
        $('#preview .uploaded').delay(300).fadeIn(300).hide(300, function () {
            $(this).remove();
        });
        $('#preview .file').delay(1200).show(1200, function () {
            $(this).removeClass('done placeholder');
        });

        updatepreview();
    });

    /**
     * Init the dropbox
     **/
    function initDropbox(dropbox) {
        dropbox.filedrop({
            paramname: 'pic',
            fallback_id: 'upload_input',
            maxfiles: 30,
            maxfilesize: Dropfiles.maxfilesize,
            queuefiles: 2,
            data: {
                id_category: function () {
                    return $('input[name=id_category]').val();
                }
            },
            url: 'index.php?option=com_dropfiles&task=files.upload',

            uploadFinished: function (i, file, response) {
                if (typeof(response) === 'string') {
                    bootbox.alert('<div>' + response + '</div>');
                }
                if (response.response === true) {
                    $.data(file).addClass('done');
                    $.data(file).find('img').data('id-file', response.datas.id_file);
                } else {
                    bootbox.alert(response.response);
                    $.data(file).remove();
                }
            },

            error: function (err, file) {
                switch (err) {
                    case 'BrowserNotSupported':
                        if (files !== undefined) {
                            bootbox.alert(Joomla.JText._('COM_DROPFILES_JS_BROWSER_NOT_SUPPORT_HTML5', 'Your browser does not support HTML5 file uploads!'));
                        }
                        break;
                    case 'TooManyFiles':
                        bootbox.alert(Joomla.JText._('COM_DROPFILES_JS_TOO_ANY_FILES', 'Too many files') + '!');
                        break;
                    case 'FileTooLarge':
                        bootbox.alert(file.name + ' ' + Joomla.JText._('COM_DROPFILES_JS_FILE_TOO_LARGE', 'is too large') + '!');
                        break;
                    default:
                        break;
                }
            },

            // Called before each upload is started
            beforeEach: function (file) {
            },

            uploadStarted: function (i, file, len) {

                var preview = $('<div class="dropfiles_process_full" style="display: block;">' +
                    '<div class="dropfiles_process_run" data-w="0" style="width: 0%;"></div>' +
                    '</div>');

                var reader = new FileReader();

                reader.readAsDataURL(file);
                $('#preview .restable').after(preview);
                $.data(file, preview);
            },

            progressUpdated: function (i, file, progress) {
                $.data(file).find('.dropfiles_process_run').width(progress + '%');
            },

            afterAll: function () {
                $('#preview .progress').delay(300).fadeIn(300).hide(300, function () {
                    $(this).remove();
                });
                $('#preview .uploaded').delay(300).fadeIn(300).hide(300, function () {
                    $(this).remove();
                });
                $('#preview .file').delay(1200).show(1200, function () {
                    $(this).removeClass('done placeholder');
                });
                updatepreview();
//                    initDeleteBtn();
                $.gritter.add({text: Joomla.JText._('COM_DROPFILES_CTRL_FILES_UPLOAD_FILE_SUCCESS', 'Upload file successfully!')});
            },
            rename: function (name) {
                function fetchAscii(obj) {
                    var convertedObj = '';
                    for (i = 0; i < obj.length; i++) {
                        var asciiChar = obj.charCodeAt(i);
                        convertedObj += '&#' + asciiChar + ';';
                    }
                    return convertedObj;
                }

                return fetchAscii(name);
            }
        });
    }

    /**
     * Init the dropbox
     **/
    function initDropboxVersion(dropbox) {
        dropbox.filedrop({
            paramname: 'pic',
            fallback_id: 'upload_input_version',
            fallback_dropzoneClick: true,
            maxfiles: 1,
            maxfilesize: Dropfiles.maxfilesize,
            queuefiles: 1,
            data: {
                id_file: function () {
                    return $('.file.selected').data('id-file');
                },
                id_category: function () {
                    return $('input[name=id_category]').val();
                },
                ext: function () {
                    return $('.file.selected').find('.type').html();
                }
            },
            url: 'index.php?option=com_dropfiles&task=files.version',

            uploadFinished: function (i, file, response) {

                if (response.response === true) {

                } else {
                    bootbox.alert(response.response);
//                        $.data(file).remove();
                    $('#dropbox_version .progress').addClass('hide');
                    $('#dropbox_version .upload').removeClass('hide');
                }
            },

            error: function (err, file) {

                switch (err) {
                    case 'BrowserNotSupported':
                        if (files !== undefined) {
                            bootbox.alert(Joomla.JText._('COM_DROPFILES_JS_BROWSER_NOT_SUPPORT_HTML5', 'Your browser does not support HTML5 file uploads!'));
                        }
                        break;
                    case 'TooManyFiles':
                        bootbox.alert(Joomla.JText._('COM_DROPFILES_JS_TOO_ANY_FILES', 'Too many files') + '!');
                        break;
                    case 'FileTooLarge':
                        bootbox.alert(file.name + ' ' + Joomla.JText._('COM_DROPFILES_JS_FILE_TOO_LARGE', 'is too large') + '!');
                        break;
                    default:
                        break;
                }
            },

            // Called before each upload is started
            beforeEach: function (file) {
//                        if(!file.type.match(/^image\//)){
//                                bootbox.alert(Joomla.JText._('COM_DROPFILES_JS_ONLY_IMAGE_ALLOWED','Only images are allowed')+'!');
//                                return false;
//                        }
            },

            uploadStarted: function (i, file, len) {

                // Associating a preview container
                // with the file, using jQuery's $.data():
                $('#dropbox_version .upload').addClass('hide');
                $('#dropbox_version .progress').removeClass('hide');
//                        $.data(file,preview);
            },

            progressUpdated: function (i, file, progress) {
                $('#dropbox_version .progress .bar').width(progress + '%');
            },

            afterAll: function () {
                $('#dropbox_version .progress').addClass('hide');
                $('#dropbox_version .upload').removeClass('hide');
                id_file = $('.file.selected').data('id-file');
                updatepreview(null, id_file);
            },
            rename: function (name) {
                ext = name.substr(name.lastIndexOf('.'), name.length);
                name = name.substr(0, name.lastIndexOf('.'));
                var pattern_accent = new Array("é", "è", "ê", "ë", "ç", "à", "â", "ä", "î", "ï", "ù", "ô", "ó", "ö");
                var pattern_replace_accent = new Array("e", "e", "e", "e", "c", "a", "a", "a", "i", "i", "u", "o", "o", "o");
                name = preg_replace(pattern_accent, pattern_replace_accent, name);

                name = name.replace(/\s+/gi, '-');
                name = name.replace(/[^a-zA-Z0-9\-]/gi, '');
                return name + ext;
            }
        });
    }


    /* Title edition */
    function initMenu() {
        /**
         * Click on delete category btn
         */
        $('#categorieslist .dd-content .trash').unbind('click').on('click', function () {
            id_category = $(this).closest('li').data('id-category');
            bootbox.confirm(Joomla.JText._('COM_DROPFILES_JS_WANT_DELETE_CATEGORY', 'Do you really want to delete') + ' "' + $(this).parent().find('.title').text() + '"?', function (result) {
                if (result === true) {
                    $.ajax({
                        url: "index.php?option=com_dropfiles&task=categories.delete&id_category=" + id_category,
                        type: 'POST',
                        data: $('#categoryToken').attr('name') + '=1'
                    }).done(function (data) {
                        result = jQuery.parseJSON(data);
                        if (result.response === true) {
                            $('#mycategories #categorieslist li[data-id-category=' + id_category + ']').remove();
                            $('#preview').contents().remove();
                            first = $('#mycategories #categorieslist li dd-content').first();
                            if (first.length > 0) {
                                first.click();
                            } else {
                                $('#insertcategory').hide();
                            }
                            if ($('#categorieslist li').length === 0) {
                                $('.nested').append('<div class="dd-empty">' + Joomla.JText._('COM_DROPFILES_JS_PLEASE_CREATE_A_FOLDER', 'Please create a folder') + '</div>');
                                $('input[name=id_category]').val('');
                                $('#galleryparams').contents().remove();
                            }
                            $.gritter.add({text: Joomla.JText._('COM_DROPFILES_JS_CATEGORY_REMOVED', 'Category removed with success!')});
                        } else {
                            bootbox.alert(result.response);
                        }
                    });
                }
            });
            return false;
        });

        $('#categorieslist .dd-content .sync').unbind('click').on('click', function () {
            $(this).closest('div.dd-content').find('a.t').append('<i class="icon-syncing"></i>');
            id_category = $(this).closest('li').data('id-category');
            $.ajax({
                url: "index.php?option=com_dropfiles&task=googledrive.syncCategory&id_category=" + id_category,
                type: 'POST',
                data: $('#categoryToken').attr('name') + '=1'
            }).done(function (data) {
                console.log(data);
                if(data.status) {
                    syncGoogleFiles(0, id_category);
                }
                //result = jQuery.parseJSON(data);

            });
        });

        /* Set the active category on menu click */
        $('#categorieslist .dd-content').unbind('click').click(function (e) {
            id_category = $(this).parent().data('id-category');
            $('input[name=id_category]').val(id_category);
            if (Dropfiles.catRefTofileId) {
                updatepreview(id_category, Dropfiles.catRefTofileId);
                Dropfiles.catRefTofileId = false;
            } else {
                updatepreview(id_category);
                Dropfiles.catRefTofileId = false;
            }
            $('#categorieslist li').removeClass('active');
            $(this).parent().addClass('active');
            var event = $.Event('dropfiles_category_click');
            $(this).trigger(event);
            if ($(this).parent().find('.google-drive-icon').length > 0) {
                $('#rightcol .fileblock').addClass('googleblock');
                $('#rightcol .categoryblock').addClass('catgoogleblock');
            } else {
                $('#rightcol .fileblock').removeClass('googleblock');
                $('#rightcol .categoryblock').removeClass('catgoogleblock');
            }
            return false;
        });

        $('#categorieslist .dd-content a.edit').unbind().click(function (e) {
            e.stopPropagation();
            $this = this;
            link = $(this).parent().find('a span.title');
            link2 = $(this).parent().find('a.t');
            oldTitle = link.text();
            $(link).attr('contentEditable', true);
            $(link).addClass('editable');
            $(link2).addClass('editable');
            $(link).selectText();

            $('#categorieslist a span.editable').bind('click.mm', hstop);  //let's click on the editable object
            $(link).bind('keypress.mm', hpress); //let's press enter to validate new title'
            $('*').not($(link)).bind('click.mm', houtside);

            function unbindall() {
                $('#categorieslist a span').unbind('click.mm', hstop);  //let's click on the editable object
                $(link).unbind('keypress.mm', hpress); //let's press enter to validate new title'
                $('*').not($(link)).unbind('click.mm', houtside);
            }

            //Validation
            function hstop(event) {
                event.stopPropagation();
                return false;
            }

            //Press enter
            function hpress(e) {
                if (e.which == 13) {
                    e.preventDefault();
                    unbindall();
                    updateTitle($(link).text());
                    $(link).removeAttr('contentEditable');
                    $(link).removeClass('editable');
                    $(link2).removeClass('editable');
                }
            }

            //click outside
            function houtside(e) {
                unbindall();
                updateTitle($(link).text());
                $(link).removeAttr('contentEditable');
                $(link).removeClass('editable');
                $(link2).removeClass('editable');
            }


            function updateTitle(title) {
                id_category = $(link).parents('li').data('id-category');
                if (title !== '') {
                    $.ajax({
                        url: "index.php?option=com_dropfiles&task=category.setTitle&id_category=" + id_category + '&title=' + encodeURIComponent(title),
                        type: "POST"
                    }).done(function (data) {
                        result = jQuery.parseJSON(data);
                        if (result === true) {
                            $.gritter.add({text: Joomla.JText._('COM_DROPFILES_JS_CATEGORY_RENAMED', 'Category renamed with success!')});
                            return true;
                        }
                        $(link).text(oldTitle);
                        return false;
                    });
                } else {
                    $(link).text(oldTitle);
                    return false;
                }
                $(link).parent().css('white-space', 'normal');
                setTimeout(function () {
                    $(link).parent().css('white-space', '');
                }, 200);

            }
        });
    }

    (function () {
        $('#patchHtaccess').click(function () {
            $.ajax({
                url: 'index.php?option=com_dropfiles&view=patch&tmpl=component&format=raw'
            }).done(function (data) {
                bootbox.alert(data);
            });
        });
    })();

    function loading(e) {
        $(e).addClass('dploadingcontainer');
        $(e).append('<div class="dploading"></div>');
    }

    function rloading(e) {
        $(e).removeClass('dploadingcontainer');
        $(e).find('div.dploading').remove();
    }

    function  mainSettingsToggle() {
        var headdingControll = $('.themesblock .heading-section');
        if(headdingControll) {
            headdingControll.each(function () {
                $(this).on("click", function(e){
                    e.preventDefault();
                    $(this).toggleClass('collapsed');
                    $(this).siblings('.control-group').slideToggle();
                });
            });
        }
    }

    mainSettingsToggle();
});

/**
 * Insert the current category into a content editor
 */
function insertCategorytoEditor(editor_name) {
    var tag =  insertCategory();
    /** Use the API, if editor supports it **/
    if (window.parent.Joomla && window.parent.Joomla.editors && window.parent.Joomla.editors.instances && window.parent.Joomla.editors.instances.hasOwnProperty(editor_name))
    {
        window.parent.Joomla.editors.instances[editor_name].replaceSelection(tag);
    }
    else
    {
        window.parent.jInsertEditorText(tag, editor_name);
    }

    // Close modal
    if (window.parent.CKEDITOR) { // DropEditor or Ckeditor
        var ckDialog = window.parent.CKEDITOR.dialog.getCurrent();
        if(ckDialog) {
            ckDialog.hide();
        }
    } else if (typeof window.parent.Joomla.Modal !== 'undefined') {
        window.parent.Joomla.Modal.getCurrent().close();
    }
    else if (window.parent.SqueezeBox) { // TinyMCE
        window.parent.SqueezeBox.close();
    }
}

/**
 * Insert the current category into a content editor
 */
function insertCategory() {
    id_category = jQuery('input[name=id_category]').val();

    dir = decodeURIComponent(getUrlVar('path'));
    code = '<img src="' + dir + '/components/com_dropfiles/assets/images/t.gif"' +
        ' data-dropfilescategory="' + id_category + '"' +
        ' style="background: url(' + dir + '/components/com_dropfiles/assets/images/folder_download.png) no-repeat scroll center center #444444;' +
        'height: 200px;' +
        'border-radius: 10px;' +
        'width: 99%;" data-category="' + id_category + '" />';
    return code;
}


/**
 * Insert the current file into a content editor
 */
function insertFiletoEditor(editor_name) {
    var tag =  insertFile();
    /** Use the API, if editor supports it **/
    if (window.parent.Joomla && window.parent.Joomla.editors && window.parent.Joomla.editors.instances && window.parent.Joomla.editors.instances.hasOwnProperty(editor_name))
    {
        window.parent.Joomla.editors.instances[editor_name].replaceSelection(tag);
    }
    else
    {
        window.parent.jInsertEditorText(tag, editor_name);
    }

    // Close modal
    if (window.parent.CKEDITOR) { // DropEditor or Ckeditor
        var ckDialog = window.parent.CKEDITOR.dialog.getCurrent();
        if(ckDialog) {
            ckDialog.hide();
        }
    } else if (typeof window.parent.Joomla.Modal !== 'undefined') {
        window.parent.Joomla.Modal.getCurrent().close();
    }
    else if (window.parent.SqueezeBox) { // TinyMCE
        window.parent.SqueezeBox.close();
    }
}

/**
 * Insert the current file into a content editor
 */
function insertFile() {
    id_file = jQuery('.file.selected').data('id-file');
    id_category = jQuery('input[name=id_category]').val();
    dir = decodeURIComponent(getUrlVar('path'));
    code = '<img src="' + dir + '/components/com_dropfiles/assets/images/t.gif"' +
        ' data-dropfilesfile="' + id_file + '"' +
        ' data-dropfilesfilecategory="' + id_category + '"' +
        ' style="background: url(' + dir + '/components/com_dropfiles/assets/images/file_download.png) no-repeat scroll center center #444444;' +
        'height: 100px;' +
        'border-radius: 10px;' +
        'width: 99%;" data-file="' + id_file + '" />';
    return code;
}

//From http://jquery-howto.blogspot.fr/2009/09/get-url-parameters-values-with-jquery.html
function getUrlVars() {
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for (var i = 0; i < hashes.length; i++) {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}

function getUrlVar(v) {
    if (typeof(getUrlVars()[v]) !== "undefined") {
        return getUrlVars()[v];
    }
    return null;
}

function preg_replace(array_pattern, array_pattern_replace, my_string) {
    var new_string = String(my_string);
    for (i = 0; i < array_pattern.length; i++) {
        var reg_exp = RegExp(array_pattern[i], "gi");
        var val_to_replace = array_pattern_replace[i];
        new_string = new_string.replace(reg_exp, val_to_replace);
    }
    return new_string;
}

//https://gist.github.com/ncr/399624
jQuery.fn.single_double_click = function (single_click_callback, double_click_callback, timeout) {
    return this.each(function () {
        var clicks = 0, self = this;
        jQuery(this).click(function (event) {
            clicks++;
            if (clicks == 1) {
                setTimeout(function () {
                    if (clicks == 1) {
                        single_click_callback.call(self, event);
                    } else {
                        double_click_callback.call(self, event);
                    }
                    clicks = 0;
                }, timeout || 300);
            }
        });
    });
}


function jInsertFieldValue(value, id) {
    var $ = jQuery.noConflict();
    var old_value = $("#" + id).val();
    if (old_value != value) {
        var $elem = $("#" + id);
        $elem.val(value);
        $elem.trigger("change");
        if (typeof($elem.get(0).onchange) === "function") {
            $elem.get(0).onchange();
        }
        jMediaRefreshPreview(id);
    }
}

function jMediaRefreshPreview(id) {
    var $ = jQuery.noConflict();
    var value = $("#" + id).val();
    var $img = $("#" + id + "_preview");
    var basepath = $("#" + id).data("basepath");

    if ($img.length) {
        if (value) {
            $img.attr("src", basepath + value);
            $("#" + id + "_preview_empty").hide();
            $("#" + id + "_preview_img").show()
        } else {
            $img.attr("src", "");
            $("#" + id + "_preview_empty").show();
            $("#" + id + "_preview_img").hide();
        }
    }
}

function jModalClose() {
    SqueezeBox.close();
}

var getUrlParameter = function getUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : sParameterName[1];
        }
    }
};

// Close bootrap modal or manually hide element
window.closeModal = function(id){
    var $ = jQuery.noConflict();
    var wpfdModal;
    if (typeof bootstrap !== 'undefined') {
        wpfdModal = bootstrap.Modal.getInstance($('#'+id));
    }
    if (wpfdModal) {
        wpfdModal.hide();
    } else {
        if ($('#'+id)) {
            $('#'+id).hide();
        }
        $('.modal-backdrop').remove();
    }
};


window.multipleUser = {};
window.multipleUser.setValue = function(value, name) {
    var $ = jQuery.noConflict();
    var $input = $("#jform_canview_id");
    var $inputName = $("#jform_canview");
    var oldValue = $input.val();
    var oldName = $inputName.val();
    if (oldValue === '0' || oldValue === '') {
        $input.val(value).trigger('change');
        $inputName.val(name || value).trigger('change');
    } else {
        var newValue = oldValue.split(',');
        var newName = oldName.split(',');
        newValue.push(value);
        newName.push(name);
        $input.val(newValue.unique().join(',')).trigger('change');
        $inputName.val(newName.unique().join(',')).trigger('change');
    }
};

window.multipleUser.unsetValue = function(value, name) {
    var $ = jQuery.noConflict();
    var $input = $("#jform_canview_id");
    var $inputName = $("#jform_canview");
    var oldValue = $input.val().split(',');
    var oldName = $inputName.val().split(',');

    if (oldValue.length === 0) {
        $input.val(0).trigger('change');
        $inputName.val('').trigger('change');
    } else {
        var newValue = $.grep(oldValue, function(item, index) {
            return item.toString() !== value.toString();
        });
        var newName = $.grep(oldName, function(item, index) {
            return item.toString() !== name.toString();
        });

        $input.val(newValue.unique().join(',')).trigger('change');
        $inputName.val(newName.unique().join(',')).trigger('change');
    }
};

/**
 * Array unique function from
 * Thanks to ShAkKiR from https://stackoverflow.com/a/44376705
 * @returns {Array}
 */
Array.prototype.unique = function() {
    var a = [];
    for (i = 0; i < this.length; i++) {
        var current = this[i];
        if (a.indexOf(current) < 0) a.push(current);
    }
    return a;
}