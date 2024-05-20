/**
 * File Upload Element
 *
 * @copyright: Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license: GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */


/**
 *
 * @param {Integer} elementId
 * @param {Integer} attachId
 * Loads uploaded attachments linked to an element Id
 */
function watch(elementId, attachId) {
    var myFormData = new FormData();
    var divCtrlGroup = document.querySelector('div.fb_el_'+elementId);
    var div = document.querySelector('div#div_'+elementId);
    var fnum = document.querySelector('input#'+elementId.split('___')[0]+'___fnum').value;
    myFormData.append('attachId', attachId);
    myFormData.append('fnum', fnum);

    var xhr = new XMLHttpRequest();

    var divAttachment = document.createElement('div');
    divAttachment.setAttribute("id", elementId + '_attachment');
    divAttachment.setAttribute("class", 'em-fileAttachment');
    div.appendChild(divAttachment);

    xhr.open('POST', 'index.php?option=com_fabrik&format=raw&task=plugin.pluginAjax&plugin=emundus_fileupload&method=ajax_attachment', true);
    xhr.onreadystatechange = function() {

        if (xhr.readyState == 4 && xhr.status == 200) {

            if(xhr.responseText != '') {

                var result = JSON.parse(xhr.responseText);

                if (result != null) {
                    if (result.limitObtained) {
                        div.querySelector('div .btn-upload').hide();
                        div.querySelector('input#'+elementId).hide();
                        divCtrlGroup.querySelector('.control-label').style.cursor = 'default';
                    } else {
                        div.querySelector('div .btn-upload').show();
                        div.querySelector('input#'+elementId).show();
                        divCtrlGroup.querySelector('.control-label').style.cursor = 'pointer';
                    }

                    if (result.files) {
                        if (!div.querySelector('.em-fileAttachmentTitle')) {
                            var attachmentTitle = document.createElement('span');
                            attachmentTitle.setAttribute("class", 'em-fileAttachmentTitle em-mt-8');
                            attachmentTitle.innerText= Joomla.JText._('PLG_ELEMENT_FILEUPLOAD_UPLOADED_FILES');
                            divAttachment.appendChild(attachmentTitle);
                        } else {
                            attachmentTitle = div.querySelector('.em-fileAttachmentTitle');
                        }

                        for (var i = 0; i < result.files.length; i++) {
                            var divLink = document.createElement('div');
                            divLink.setAttribute("id", elementId + '_attachment_link' + i);
                            divLink.setAttribute("class", 'em-fileAttachment-link');

                            if (!document.getElementById(divLink.id)) {
                                divAttachment.appendChild(divLink);
                            }

                            if (result.files[i].can_be_viewed == 1) {
                                var link = document.createElement('a');
                                link.setAttribute("href", result.files[i].target);
                                link.setAttribute("target", "_blank");
                            } else {
                                var link = document.createElement('p');
                            }
                            var linkText = document.createTextNode(result.files[i].local_filename);

                            divLink.appendChild(link);
                            link.appendChild(linkText);

                            if (result.files[i].can_be_deleted == 1) {
                                var deleteButton = document.createElement('a');
                                deleteButton.setAttribute("class", 'em-pointer em-deleteFile em-ml-8');
                                deleteButton.setAttribute('value', result.files[i].filename);

                                var deleteIcon = document.createElement('span');
                                deleteIcon.setAttribute("class", 'material-icons-outlined');
                                deleteIcon.setAttribute("style",'font-size: 16px');
                                deleteIcon.appendChild(document.createTextNode('clear'));
                                deleteButton.appendChild(deleteIcon);

                                divLink.appendChild(deleteButton);

                                var button = document.querySelector('#' + elementId + '_attachment_link' + i + ' > a.em-deleteFile');
                                button.addEventListener('click', () => FbFileUpload.delete(elementId, attachId));
                            }
                        }
                    }

                }
            }
        }
    };
    xhr.send(myFormData);
}

var FbFileUpload = {
    initialize: function (element, options) {

        var self = this;
        this.setPlugin('emundus_fileupload');
        this.parent(element, options);
        this.container = jQuery(this.container);
        this.toppath = this.options.dir;
        if (this.options.folderSelect === '1' && this.options.editable === true) {
            this.ajaxFolder();
        }

        this.doBrowseEvent = null;
        this.watchBrowseButton();

        if (this.options.ajax_upload && this.options.editable !== false) {
            Fabrik.fireEvent('fabrik.fileupload.plupload.build.start', this);
            this.watchAjax();
            if (Object.keys(this.options.files).length !== 0) {
                this.uploader.trigger('FilesAdded', this.options.files);
                jQuery.each(this.options.files, function (key, file) {
                    var response = {
                            filepath: file.path,
                            uri: file.url,
                            showWidget: false
                        },
                        newBar = jQuery(Fabrik.jLayouts['fabrik-progress-bar-success'])[0],
                        bar = jQuery('#' + file.id).find('.bar')[0];
                    self.uploader.trigger('UploadProgress', file);
                    self.uploader.trigger('FileUploaded', file, {
                        response: JSON.stringify(response)
                    });

                    jQuery(bar).replaceWith(newBar);
                });
            }
            this.redraw();
        }

        this.doDeleteEvent = null;
        this.watchDeleteButton();
        this.watchTab();
    },

    /**
     * Reposition the hidden input field over the 'add' button. Called on initiate and if in a tab
     * and the tab is activated. Triggered from element.watchTab()
     */
    redraw: function () {
        var el = jQuery(this.element);
        if (this.options.ajax_upload) {
            var browseButton = jQuery('#' + el.prop('id') + '_browseButton'),
                c = jQuery('#' + this.options.element + '_container'),
                diff = browseButton.position().left - c.position().left;
            /*  $$$ hugh - working on some IE issues */
            var file_element = c.closest('.fabrikElement').find('input[type=file]');
            if (file_element.length > 0) {
                var fileContainer = file_element.parent();
                fileContainer.css({
                    'width': browseButton.width(),
                    'height': browseButton.height()
                });
                fileContainer.css('top', diff);
            }
        }
    },

    doBrowse: function (evt) {
        if (window.File && window.FileReader && window.FileList && window.Blob) {
            var reader, self = this,
                files = evt.target.files,
                f = files[0];

            /*  Only process image files. */
            if (f.type.match('image.*')) {
                reader = new FileReader();
                /*  Closure to capture the file information. */
                reader.onload = (function (theFile) {
                    return function (e) {
                        var c = jQuery(self.getContainer()),
                            b = c.find('img');
                        b.attr('src', e.target.result);
                        var d = b.closest('.fabrikHide');
                        d.removeClass('fabrikHide');
                        var db = c.find('[data-file]');
                        db.addClass('fabrikHide');
                    };
                }.bind(this))(f);
                /*  Read in the image file as a data URL. */
                reader.readAsDataURL(f);

            } else if (f.type.match('video.*')) {

                var c = jQuery(this.getContainer()),
                    video = c.find('video');
                if (video.length > 0) {
                    video = this.makeVideoPreview();
                    video.appendTo(c);
                }

                reader = new window.FileReader();
                var url;

                reader = window.URL || window.webKitURL;

                if (reader && reader.createObjectURL) {
                    url = reader.createObjectURL(f);
                    video.attr('src', url);
                    return;
                }

                if (!window.FileReader) {
                    console.log('Sorry, not so much');
                    return;
                }

                reader = new window.FileReader();
                reader.onload = function (eo) {
                    video.attr('src', eo.target.result);
                };
                reader.readAsDataURL(f);
            }
        }
    },



    watchFileAttachment: function(elementId, attachId) {
        return watch(elementId, attachId);
    },

    upload: function(elementId, attachId, size, encrypt) {

        var myFormData = new FormData();
        var input = document.querySelector('div#div_'+elementId+' > input#'+elementId);
        var div = document.querySelector('div#div_'+elementId);
        var deleteButton = document.querySelector('div#div_'+elementId+' > a.em-deleteFile');
        var fnum = document.querySelector('input#'+elementId.split('___')[0]+'___fnum').value;

        myFormData.append('attachId', attachId);
        myFormData.append('elementId', elementId);
        myFormData.append('fnum', fnum);
        myFormData.append('size', size);
        myFormData.append('encrypt', encrypt);

        var file = [];
        for (var i = 0; i < input.files.length; i++) {
            file = input.files[i];
            myFormData.append('file[]', file);
        }

        var xhr = new XMLHttpRequest();
        /*  Add any event handlers here... */
        xhr.onreadystatechange = function() {

            if (xhr.readyState==4 && xhr.status==200) {

                var result = JSON.parse(xhr.responseText);

                if(result.status == false){
                    Swal.fire({
                        type: 'error',
                        title: Joomla.JText._('PLG_ELEMENT_FIELD_ERROR'),
                        text: Joomla.JText._('PLG_ELEMENT_FIELD_ERROR_TEXT'),
                        customClass: {
                            title: 'em-swal-title',
                            confirmButton: 'em-swal-confirm-button',
                            actions: "em-swal-single-action",
                        }
                    });
                }

                for (var j = 0; j < result.length; j++) {
                    if (result[j].ext == true && result[j].size == true && result[j].nbMax == false) {
                        var inputHidden = document.createElement('input');
                        inputHidden.setAttribute("type", "hidden");
                        inputHidden.setAttribute("name", elementId + '_filename' + j);
                        inputHidden.setAttribute("value", result[j].filename);
                        div.appendChild(inputHidden);

                        Swal.fire({
                            title: Joomla.JText._('PLG_ELEMENT_FIELD_SUCCESS'),
                            text: Joomla.JText._('PLG_ELEMENT_FIELD_UPLOAD'),
                            type: 'success',
                            showConfirmButton: false,
                            timer: 1500,
                            customClass: {
                                title: 'em-swal-title',
                            }
                        });
                    }

                    if (result[j].ext == false) {
                        Swal.fire({
                            type: 'error',
                            title: Joomla.JText._('PLG_ELEMENT_FIELD_ERROR'),
                            text: Joomla.JText._('PLG_ELEMENT_FIELD_EXTENSION'),
                            customClass: {
                                title: 'em-swal-title',
                                confirmButton: 'em-swal-confirm-button',
                                actions: "em-swal-single-action",
                            }
                        });

                        input.value = '';
                        if(deleteButton != null) {
                            deleteButton.style.display = 'none';
                        }
                    }

                    if (result[j].encrypt == false){
                        Swal.fire({
                            type: 'error',
                            title: Joomla.JText._('PLG_ELEMENT_FIELD_ERROR'),
                            text: Joomla.JText._('PLG_ELEMENT_FIELD_ENCRYPT'),
                            customClass: {
                                title: 'em-swal-title',
                                confirmButton: 'em-swal-confirm-button',
                                actions: "em-swal-single-action",
                            }
                        });
                        input.value = '';
                    }

                    if (result[j].size == false) {
                        Swal.fire({
                            type: 'error',
                            title: Joomla.JText._('PLG_ELEMENT_FIELD_ERROR'),
                            text: Joomla.JText._('PLG_ELEMENT_FIELD_SIZE')+result[j].maxSize,
                            customClass: {
                                title: 'em-swal-title',
                                confirmButton: 'em-swal-confirm-button',
                                actions: "em-swal-single-action",
                            }
                        });
                        input.value = '';
                        deleteButton.style.display = 'none';
                    }

                    if (result[j].nbMax == true) {
                        Swal.fire({
                            type: 'error',
                            title: Joomla.JText._('PLG_ELEMENT_FIELD_ERROR'),
                            text: Joomla.JText._('PLG_ELEMENT_FIELD_LIMIT'),
                            customClass: {
                                title: 'em-swal-title',
                                confirmButton: 'em-swal-confirm-button',
                                actions: "em-swal-single-action",
                            }
                        });
                        input.value = '';
                        deleteButton.style.display = 'none';
                    }
                }
                watch(elementId, attachId);
            } else if(xhr.status == 500){
                Swal.fire({
                    type: 'error',
                    title: Joomla.JText._('PLG_ELEMENT_FIELD_ERROR'),
                    text: Joomla.JText._('PLG_ELEMENT_FIELD_ERROR_TEXT'),
                    customClass: {
                        title: 'em-swal-title',
                        confirmButton: 'em-swal-confirm-button',
                        actions: "em-swal-single-action",
                    }
                });
                input.value = '';
                deleteButton.style.display = 'none';
            }
        };
        xhr.open('POST', 'index.php?option=com_fabrik&format=raw&task=plugin.pluginAjax&plugin=emundus_fileupload&method=ajax_upload', true);
        xhr.send(myFormData);
    },

    delete: function(elementId, attachId) {
        var div_parent = document.querySelector('div#div_'+elementId);
        var file = event.target;
        var local_filename = file.parentElement.parentElement.firstChild.innerText;
        var fileName = file.parentElement.parentElement.firstChild.href.split('/');
        fileName = fileName[fileName.length - 1];

        Swal.fire({
            title: Joomla.JText._('PLG_ELEMENT_FIELD_SURE'),
            html: Joomla.JText._('PLG_ELEMENT_FIELD_SURE_TEXT') + '<strong>'+ local_filename + '</strong> ?',
            type: 'warning',
            showCancelButton: true,
            reverseButtons: true,
            confirmButtonText: Joomla.JText._('PLG_ELEMENT_FIELD_CONFIRM'),
            cancelButtonText: Joomla.JText._('PLG_ELEMENT_FIELD_CANCEL'),
            customClass: {
                title: 'em-swal-title',
                cancelButton: 'em-swal-cancel-button',
                confirmButton: 'em-swal-confirm-button',
            }
        }).then(answser => {
            if (answser.value) {
                var div = document.querySelector('div#'+elementId+'_attachment');
                var input = document.querySelector('div#div_'+elementId+' > input#'+elementId);
                var fnum = document.querySelector('input#'+elementId.split('___')[0]+'___fnum').value;

                const formData = new FormData();
                formData.append('filename', fileName);
                formData.append('attachId', attachId);
                formData.append('fnum', fnum);

                fetch('index.php?option=com_fabrik&format=raw&task=plugin.pluginAjax&plugin=emundus_fileupload&method=ajax_delete', {
                    body: formData,
                    method: 'post'
                }).then((response) => {
                    if (response.ok) {
                        return response.json();
                    }
                }).then((res) => {
                    if (res.status) {
                        div_parent.querySelector('div.btn-upload').show();
                        div_parent.querySelector('input#'+elementId).show();

                        file.parentElement.parentElement.remove();

                        var attachmentList = div_parent.querySelectorAll('.em-fileAttachment-link').length;
                        if (attachmentList === 0) {
                            document.querySelector('div#'+elementId+'_attachment > .em-fileAttachmentTitle').remove();
                        }

                        input.value = '';
                        Swal.fire({
                            title: Joomla.JText._('PLG_ELEMENT_FIELD_DELETE'),
                            text: Joomla.JText._('PLG_ELEMENT_FIELD_DELETE_TEXT'),
                            type: 'success',
                            customClass: {
                                title: 'em-swal-title',
                                confirmButton: 'em-swal-confirm-button',
                                actions: 'em-swal-single-action',
                            }
                        });
                    } else {
                        Swal.fire({
                            title: Joomla.JText._('PLG_ELEMENT_FIELD_DELETE_FAILED'),
                            text: Joomla.JText._('PLG_ELEMENT_FIELD_DELETE_TEXT_FAILED'),
                            type: 'error',
                            customClass: {
                                title: 'em-swal-title',
                                confirmButton: 'em-swal-confirm-button',
                                actions: 'em-swal-single-action',
                            }
                        });
                    }
                });
            }
        });
    },

    watchBrowseButton: function () {
        var el = jQuery(this.element);
        if (this.options.useWIP && !this.options.ajax_upload && this.options.editable !== false) {
            el.off('change', this.doBrowseEvent);
            this.doBrowseEvent = this.doBrowse.bind(this);
            el.on('change', this.doBrowseEvent);
        }
    },

    /**
     * Called from watchDeleteButton
     *
     * @param {Event} e
     */
    doDelete: function (e) {
        e.preventDefault();
        var c = jQuery(this.getContainer()),
            self = this,
            b = c.find('[data-file]');

        if (window.confirm(Joomla.JText._('PLG_ELEMENT_FILEUPLOAD_CONFIRM_SOFT_DELETE'))) {
            var joinPkVal = b.data('join-pk-val');
            new jQuery.ajax({
                url: '',
                data: {
                    'option': 'com_fabrik',
                    'format': 'raw',
                    'task': 'plugin.pluginAjax',
                    'plugin': 'emundus_fileupload',
                    'method': 'ajax_clearFileReference',
                    'element_id': this.options.id,
                    'formid': this.form.id,
                    'rowid': this.form.options.rowid,
                    'joinPkVal': joinPkVal
                }
            }).done(function () {
                Fabrik.trigger('fabrik.fileupload.clearfileref.complete', self);
            });

            if (window.confirm(Joomla.JText._('PLG_ELEMENT_FILEUPLOAD_CONFIRM_HARD_DELETE'))) {
                this.makeDeletedImageField(this.groupid, b.data('file')).appendTo(c);
                Fabrik.fireEvent('fabrik.fileupload.delete.complete', this);
            }

            b.remove();
            var el = jQuery(this.element);
            var i = el.closest('.fabrikElement').find('img');
            i.attr('src', this.options.defaultImage !== '' ? Fabrik.liveSite + this.options.defaultImage : '');
        }
    },

    /**
     * Single file uploads can allow the user to delete the reference and/or file
     */
    watchDeleteButton: function () {
        var c = jQuery(this.getContainer()),
            b = c.find('[data-file]');
        b.off('click', this.doDeleteEvent);
        this.doDeleteEvent = this.doDelete.bind(this);
        b.on('click', this.doDeleteEvent);
    },

    /**
     * Sets the element key used in Fabrik.blocks.form_X.formElements overwritten by dbjoin rendered as checkbox
     *
     * @since 3.0.7
     *
     * @return string
     */
    getFormElementsKey: function (elId) {
        this.baseElementId = elId;
        if (this.options.ajax_upload && this.options.ajax_max > 1) {
            return this.options.listName + '___' + this.options.elementShortName;
        } else {
            return this.parent(elId);
        }
    },

    /**
     * When in ajax form, on submit the list will call this, so we can remove the submit event if we dont do that, upon a second form submission the
     * original submitEvent is used causing a js error as it still references the files uploaded in the first form
     */
    removeCustomEvents: function () {
        /*  Fabrik.removeEvent('fabrik.form.submit.start', this.submitEvent); */
    },

    cloned: function (c) {
        var el = jQuery(this.element);
        /*  replaced cloned image with default image */
        if (el.closest('.fabrikElement').length === 0) {
            return;
        }
        var i = el.closest('.fabrikElement').find('img');
        i.attr('src', this.options.defaultImage !== '' ? Fabrik.liveSite + this.options.defaultImage : '');
        jQuery(this.getContainer()).find('[data-file]').remove();
        this.watchBrowseButton();
        this.parent(c);
    },

    decloned: function (groupid) {
        var i = jQuery('#form_' + this.form.id).find('input[name=fabrik_deletedimages[' + groupid + ']]');
        if (i.length > 0) {
            this.makeDeletedImageField(groupid, this.options.value).inject(this.form.form);
        }
    },

    decreaseName: function (delIndex) {
        var f = this.getOrigField();
        if (typeOf(f) !== 'null') {
            f.name = this._decreaseName(f.name, delIndex);
            f.id = this._decreaseId(f.id, delIndex);
        }
        return this.parent(delIndex);
    },

    getOrigField: function () {
        var p = this.element.getParent('.fabrikElement');
        var f = p.getElement('input[name^=' + this.origId + '_orig]');
        if (typeOf(f) === 'null') {
            f = p.getElement('input[id^=' + this.origId + '_orig]');
        }
        return f;
    },

    /**
     * Create a hidden input which will tell fabrik, upon form submission, to delete the file
     *
     * @param {int} groupId group id
     * @param {string} value file to delete
     *
     * @return Element DOM Node - hidden input
     */
    makeDeletedImageField: function (groupId, value) {
        return jQuery(document.createElement('input')).attr({
            'type': 'hidden',
            'name': 'fabrik_fileupload_deletedfile[' + groupId + '][]',
            'value': value
        });
    },

    makeVideoPreview: function () {
        var el = jQuery(this.element);
        return jQuery(document.createElement('video')).attr({
            'id': el.prop('id') + '_video_preview',
            'controls': true
        });
    },

    update: function (val) {
        if (this.element) {
            var el = jQuery(this.element);
            if (val === '') {
                if (this.options.ajax_upload) {
                    this.uploader.files = [];
                    el.parent().find('[id$=_dropList] tr').remove();
                } else {
                    el.val('');
                }
            } else {
                var img = el.closest('div.fabrikSubElementContainer').find('img');
                if (img) {
                    img.prop('src', val);
                }
            }
        }
    },

    addDropArea: function () {
        if (!Fabrik.bootstraped) {
            return;
        }
        var dropTxt = this.container.find('tr.plupload_droptext'), tr;
        if (dropTxt.length > 0) {
            dropTxt.show();
        } else {
            tr = jQuery(document.createElementget('tr')).addClass('plupload_droptext').html('<td colspan="4"><i class="icon-move"></i> ' + Joomla.JText
                ._('PLG_ELEMENT_FILEUPLOAD_DRAG_FILES_HERE') + ' </td>');
            this.container.find('tbody').append(tr);
        }
        this.container.find('thead').hide();
    },

    removeDropArea: function () {
        this.container.find('tr.plupload_droptext').hide();
    },

    watchAjax: function () {
        if (this.options.editable === false) {
            return;
        }
        var a, self = this,
            elementId = jQuery(this.element).prop('id'),
            el = jQuery(this.getElement());
        if (el.length === 0) {
            return;
        }
        var c = el.closest('.fabrikSubElementContainer');
        this.container = c;

        if (this.options.canvasSupport !== false) {
            this.widget = new ImageWidget(this.options.modalId, {

                'imagedim': {
                    x: 200,
                    y: 200,
                    w: this.options.winWidth,
                    h: this.options.winHeight
                },

                'cropdim': {
                    w: this.options.cropwidth,
                    h: this.options.cropheight,
                    x: this.options.winWidth / 2,
                    y: this.options.winHeight / 2
                },
                crop: this.options.crop,
                modalId: this.options.modalId,
                quality: this.options.quality
            });
        }
        this.pluploadContainer = c.find('.plupload_container');
        this.pluploadFallback = c.find('.plupload_fallback');
        this.droplist = c.find('.plupload_filelist');
        var url = 'index.php?option=com_fabrik&format=raw&task=plugin.pluginAjax';
        url += '&plugin=fileupload&' + this.options.ajaxToken + '=1';
        url += '&method=ajax_upload&element_id=' + this.options.elid;

        if (this.options.isAdmin) {
            url = 'administrator/' + url;
        }

        var plupopts = {
            runtimes: this.options.ajax_runtime,
            browse_button: elementId + '_browseButton',
            container: elementId + '_container',
            drop_element: elementId + '_dropList_container',
            url: url,
            max_file_size: this.options.max_file_size + 'kb',
            unique_names: false,
            flash_swf_url: this.options.ajax_flash_path,
            silverlight_xap_url: this.options.ajax_silverlight_path,
            chunk_size: this.options.ajax_chunk_size + 'kb',
            dragdrop: true,
            multipart: true,
            filters: this.options.filters,
            page_url: this.options.page_url
        };
        this.uploader = new plupload.Uploader(plupopts);

        /*  (1) INIT ACTIONS */
        this.uploader.bind('Init', function (up, params) {
            /*  FORCEFULLY NUKE GRACEFUL DEGRADING FALLBACK ON INIT */
            self.pluploadFallback.remove();
            self.pluploadContainer.removeClass('fabrikHide');

            if (up.features.dragdrop && up.settings.dragdrop) {
                self.addDropArea();
            }

        });

        /*
         */
        this.uploader.bind('FilesRemoved', function (up, files) {
        });

        /*  (2) ON FILES ADDED ACTION */
        this.uploader.bind('FilesAdded', function (up, files) {
            self.removeDropArea();
            var rElement = Fabrik.bootstrapped ? 'tr' : 'li', count;
            self.lastAddedFiles = files;
            if (Fabrik.bootstrapped) {
                self.container.find('thead').css('display', '');
            }
            count = self.droplist.find(rElement).length;
            jQuery.each(files, function (key, file) {
                /* files.each(function (file, idx) { */
                if (file.size > self.options.max_file_size * 1000) {
                    window.alert(Joomla.JText._('PLG_ELEMENT_FILEUPLOAD_FILE_TOO_LARGE_SHORT'));
                } else {
                    if (count >= self.options.ajax_max) {
                        window.alert(Joomla.JText._('PLG_ELEMENT_FILEUPLOAD_MAX_UPLOAD_REACHED'));
                    } else {
                        count++;
                        var a, title, innerLi;
                        if (self.isImage(file)) {
                            a = self.editImgButton();
                            if (self.options.crop) {
                                a.html(self.options.resizeButton);
                            } else {
                                a.html(self.options.previewButton);
                            }
                            title = jQuery(document.createElement('span')).text(file.name);
                        } else {
                            a = jQuery(document.createElement('span'));
                            title = jQuery(document.createElement('a')).attr({
                                'href': file.url,
                                'target': '_blank'
                            }).text(file.name);
                        }

                        innerLi = self.imageCells(file, title, a);

                        self.droplist.append(jQuery(document.createElement(rElement)).attr({
                            id: file.id,
                            'class': 'plupload_delete'
                        }).append(innerLi));
                    }
                }
            });

            /*  Automatically start the upload - need delay to ensure up.files is populated */
            setTimeout(function () {
                up.start();
            }, 100);
        });

        /*  (3) ON FILE UPLOAD PROGRESS ACTION */
        this.uploader.bind('UploadProgress', function (up, file) {
            var f = jQuery('#' + file.id);
            if (f.length > 0) {
                if (Fabrik.bootstrapped) {
                    var bar = f.find('.plupload_file_status .bar');
                    bar.css('width', file.percent + '%');
                    if (file.percent === 100) {
                        var newBar = jQuery(Fabrik.jLayouts['fabrik-progress-bar-success']);
                        bar.replaceWith(newBar);
                    }
                } else {
                    f.find('.plupload_file_status').text(file.percent + '%');
                }
            }
        });

        this.uploader.bind('Error', function (up, err) {
            self.lastAddedFiles.each(function (file) {
                var row = jQuery('#' + file.id);
                if (row.length > 0) {
                    row.remove();
                    window.alert(err.message);
                }
                self.addDropArea();
            });
        });

        this.uploader.bind('ChunkUploaded', function (up, file, response) {
            response = JSON.parse(response.response);
            if (typeof (response) === 'object') {
                if (response.error) {
                    fconsole(response.error.message);
                }
            }
        });

        this.uploader.bind('FileUploaded', function (up, file, response) {
            var name, showWidget, f, resizeButton, idValue,
                f = jQuery('#' + file.id)
            response = JSON.parse(response.response);
            if (response.error) {
                window.alert(response.error);
                f.remove();
                return;
            }

            if (f.length === 0) {
                fconsole('Filuploaded didnt find: ' + file.id);
                return;
            }
            resizeButton = f.find('.plupload_resize a');
            resizeButton.show();
            resizeButton.attr({
                href: response.uri,
                id: 'resizebutton_' + file.id
            });

            resizeButton.data('filepath', response.filepath);

            if (self.widget) {
                showWidget = response.showWidget === false ? false : true;
                self.widget.setImage(response.uri, response.filepath, file.params, showWidget);
            }

            if (self.options.inRepeatGroup) {
                name = self.options.elementName.replace(/\[\d*\]/, '[' + self.getRepeatNum() + ']');
            } else {
                name = self.options.elementName;
            }
            /*  Stores the cropparams which we need to reload the crop widget in the correct state (rotation, zoom, etc) */
            jQuery(document.createElement('input')).attr({
                'type': 'hidden',
                name: name + '[crop][' + response.filepath + ']',
                'id': 'coords_' + file.id,
                'value': JSON.stringify(file.params)
            }).insertAfter(self.pluploadContainer);


            /*  Stores the actual crop image data retrieved from the canvas */
            jQuery(document.createElement('input')).attr({
                type: 'hidden',
                name: name + '[cropdata][' + response.filepath + ']',
                'id': 'data_' + file.id
            }).insertAfter(self.pluploadContainer);

            /*  Stores the image id if > 1 fileupload */
            idValue = [file.recordid, '0'].pick();
            jQuery(document.createElement('input')).attr({
                'type': 'hidden',
                name: name + '[id][' + response.filepath + ']',
                'id': 'id_' + file.id,
                'value': idValue
            }).insertAfter(self.pluploadContainer);

            f.removeClass('plupload_file_action').addClass('plupload_done');

            self.isSubmitDone();
        });

        /*  (5) KICK-START PLUPLOAD */
        this.uploader.init();
    },

    /**
     * Create an array of the dom elements to inject into a row representing an uploaded file
     *
     * @return {array}
     */
    imageCells: function (file, title, a) {
        var del = this.deleteImgButton(), filename, status, progress, icon;
        if (Fabrik.bootstrapped) {
            icon = jQuery(document.createElement('td')).addClass(this.options.spanNames[1] + ' plupload_resize').append(a);
            progress = Fabrik.jLayouts['fabrik-progress-bar'];
            status = jQuery(document.createElement('td')).addClass(this.options.spanNames[5] + ' plupload_file_status').html(progress);
            filename = jQuery(document.createElement('td')).addClass(this.options.spanNames[6] + ' plupload_file_name').append(title);

            return [filename, icon, status, del];
        } else {
            filename = new Element('div', {
                'class': 'plupload_file_name'
            }).adopt([title, new Element('div', {
                'class': 'plupload_resize',
                style: 'display:none'
            }).adopt(a)]);
            status = new Element('div', {
                'class': 'plupload_file_status'
            }).set('text', '0%');
            var size = new Element('div', {
                'class': 'plupload_file_size'
            }).set('text', file.size);

            return [filename, del, status, size, new Element('div', {
                'class': 'plupload_clearer'
            })];
        }
    },

    /**
     * Create edit image button
     *
     * @return {jQuery}
     */
    editImgButton: function () {
        var self = this;
        if (Fabrik.bootstrapped) {
            return jQuery(document.createElement('a')).addClass('editImage').attr({
                'href': '#',
                alt: Joomla.JText._('PLG_ELEMENT_FILEUPLOAD_RESIZE')
            }).css({
                'display': 'none'
            }).on('click', function (e) {
                e.preventDefault();
                /* var a = e.target.getParent(); */
                self.pluploadResize(jQuery(this));
            });

        } else {
            return new Element('a', {
                'href': '#',
                alt: Joomla.JText._('PLG_ELEMENT_FILEUPLOAD_RESIZE'),
                events: {
                    'click': function (e) {
                        e.stop();
                        var a = e.target.getParent();
                        this.pluploadResize(jQuery(a));
                    }.bind(this)
                }
            });
        }
    },

    /**
     * Create delete image button
     *
     * @return {jQuery}
     */
    deleteImgButton: function () {
        if (Fabrik.bootstrapped) {

            var icon = Fabrik.jLayouts['fabrik-icon-delete'],
                self = this;
            return jQuery(document.createElement('td')).addClass(this.options.spanNames[1] + ' plupload_file_action').append(
                jQuery(document.createElement('a'))
                    .html(icon)
                    .attr({
                        'href': '#'
                    })
                    .on('click', function (e) {
                        e.stopPropagation();
                        self.pluploadRemoveFile(e);
                    })
            );

        } else {
            return new Element('div', {
                'class': 'plupload_file_action'
            }).adopt(new Element('a', {
                'href': '#',
                'style': 'display:block',
                events: {
                    'click': function (e) {
                        this.pluploadRemoveFile(e);
                    }.bind(this)
                }
            }));
        }
    },

    /**
     * Test if the plupload file object contains an image.
     * @param {object} file
     * @returns {*}
     */
    isImage: function (file) {
        if (file.type !== undefined) {
            return file.type === 'image';
        }
        var ext = file.name.split('.').pop().toLowerCase();
        return ['jpg', 'jpeg', 'png', 'gif'].contains(ext);
    },

    pluploadRemoveFile: function (e) {
        e.stopPropagation();
        if (!window.confirm(Joomla.JText._('PLG_ELEMENT_FILEUPLOAD_CONFIRM_HARD_DELETE'))) {
            return;
        }

        var id = jQuery(e.target).closest('tr').prop('id').split('_').pop();/*  alreadyuploaded_8_13 */
        /*  $$$ hugh - removed ' span' from the find(), as this blows up on some templates */
        var f = jQuery(e.target).closest('tr').find('.plupload_file_name').text();

        /*  Get a list of all of the uploaders files except the one to be deleted */
        var newFiles = [];
        this.uploader.files.each(function (f) {
            if (f.id !== id) {
                newFiles.push(f);
            }
        });

        /*  Update the uploader's files with the new list. */
        this.uploader.files = newFiles;

        /*  Send a request to delete the file from the server. */
        var self = this;
        var data = {
            'option': 'com_fabrik',
            'format': 'raw',
            'task': 'plugin.pluginAjax',
            'plugin': 'fileupload',
            'method': 'ajax_deleteFile',
            'element_id': this.options.id,
            'file': f,
            'recordid': id,
            'repeatCounter': this.options.repeatCounter
        };

        data[this.options.ajaxToken] = 1;

        jQuery.ajax({
            url: '',
            data: data
        }).done(function (r) {
            r = JSON.parse(r);
            if (r.error === '') {
                Fabrik.trigger('fabrik.fileupload.delete.complete', self);
                var li = jQuery(e.target).closest('.plupload_delete');
                li.remove();

                /*  Remove hidden fields as well */
                jQuery('#id_alreadyuploaded_' + self.options.id + '_' + id).remove();
                jQuery('#coords_alreadyuploaded_' + self.options.id + '_' + id).remove();

                if (jQuery(self.getContainer()).find('table tbody tr.plupload_delete').length === 0) {
                    self.addDropArea();
                }
            }
        });
    },

    /**
     *
     * @param {jQuery} a
     */
    pluploadResize: function (a) {
        if (this.widget) {
            this.widget.setImage(a.attr('href'), a.data('filepath'), {}, true);
        }
    },

    /**
     * Once the upload fires a FileUploaded bound function we test if all images for this element have been
     * uploaded If they have then we save the
     * crop widget state and fire the callback - which is handled by FbFormSubmit()
     */
    isSubmitDone: function () {
        if (this.allUploaded() && typeof (this.submitCallBack) === 'function') {
            this.saveWidgetState();
            this.submitCallBack(true);
            delete this.submitCallBack;
        }
    },

    /**
     * Called from FbFormSubmit.submit() handles testing. If not yet uploaded, triggers the
     * upload and defers the callback until the upload is
     * complete. If complete then saves widget state and calls parent onsubmit().
     */
    onsubmit: function (cb) {
        this.submitCallBack = cb;
        if (!this.allUploaded()) {
            this.uploader.start();
        } else {
            this.saveWidgetState();
            this.parent(cb);
        }
    },

    /**
     * Save the crop widget state as a json object
     */
    saveWidgetState: function () {
        if (this.widget !== undefined) {
            jQuery.each(this.widget.images, function (key, image) {
                key = key.split('\\').pop();
                var f = jQuery('input[name*="' + key + '"]').filter(function (i, fld) {
                    return fld.name.contains('[crop]');
                });
                f = f.last();

                /*  $$$ rob - seems reloading ajax fileupload element in ajax form (e.g. from db join add record) */
                /*  is producing odd effects where old fileupload object contains info to previously uploaded image? */
                if (f.length > 0) {

                    /*  Avoid circular reference in chrome when saving in ajax form */
                    var i = image.img;
                    delete (image.img);
                    f.val(JSON.stringify(image));
                    image.img = i;
                }
            });
        }
    },

    allUploaded: function () {
        var uploaded = true;
        if (this.uploader) {
            this.uploader.files.each(function (file) {
                if (file.loaded === 0) {
                    uploaded = false;
                }
            });
        }
        return uploaded;
    }
};

define(['jquery', 'fab/element'], function (jQuery, FbElement) {

    window.FbEmundus_FileUpload = new Class({
        Extends: FbElement,

        initialize: function (element, options) {
            this.setPlugin('emundus_fileupload');
            this.parent(element, options);
        },

        cloned: function (c) {
            this.parent(c);
        }
    });
    return window.FbEmundus_FileUpload;
});
