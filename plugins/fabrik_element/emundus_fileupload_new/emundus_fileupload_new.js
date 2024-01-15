define(['jquery', 'fab/element'], function (jQuery, FbElement) {
    window.FbEmundusFileUploadNew = new Class({
        Extends: FbElement,

        /**
         * Initialize object from Fabrik/Joomla
         *
         * @param element       string      name of the element
         * @param options       array       all options for the element
         */
        initialize: function (element, options) {
            this.setPlugin('emundus_fileupload_new');
            this.parent(element, options);

            this.watch(options.repeatCounter);

            document.getElementById('file_'+this.element.id).addEventListener('change', () => {
                this.upload(options.repeatCounter);
            });
        },

        watch: function (repeatCounter) {
            var input = document.querySelector('input#file_' + this.element.id);
            let divCtrlGroup = input.parentElement.parentElement.parentElement;
            let div = input.parentElement;

            let formData = new FormData();
            formData.append('uploads', document.getElementById(this.element.id).value);
            formData.append('fnum', this.options.fnum);
            formData.append('element_id', this.options.elid);
            formData.append('attachment_id', this.options.attachment_id);
            formData.append('repeatCounter', repeatCounter);

            let divAttachment = document.getElementById(this.element.id + '_attachment');
            if(!divAttachment) {
                let divAttachment = document.createElement('div');
                divAttachment.setAttribute("id", this.element.id + '_attachment');
                divAttachment.setAttribute("class", 'em-fileAttachment');

                div.appendChild(divAttachment);
            }

            fetch('index.php?option=com_fabrik&format=raw&task=plugin.pluginAjax&plugin=emundus_fileupload_new&method=ajax_attachment', {
                body: formData,
                method: 'post'
            }).then((response) => {
                if (response.ok) {
                    return response.json();
                }
            }).then((res) => {
                if (res.status) {
                    if (res.limitObtained) {
                        div.querySelector('div .btn-upload').hide();
                        div.querySelector('input#' + this.element.id).hide();
                        divCtrlGroup.querySelector('.control-label').style.cursor = 'default';
                        input.hide();
                    } else {
                        div.querySelector('div .btn-upload').show();
                        div.querySelector('input#' + this.element.id).show();
                        divCtrlGroup.querySelector('.control-label').style.cursor = 'pointer';
                        input.show();
                    }

                    res.files.forEach((file) => {
                        if(file.repeatCounter !== null) {
                            var inputHidden = document.querySelector('input#' + this.element.id);
                            if (inputHidden.value !== '') {
                                var existing_values = inputHidden.value.split(',');
                                if(existing_values.indexOf(file.filename) === -1) {
                                    inputHidden.value += ',';
                                    inputHidden.setAttribute("value", inputHidden.value + file.filename);
                                }
                            } else {
                                inputHidden.setAttribute("value", file.filename);
                            }
                        }
                    });

                    this.loadFilesBlock(res.files);
                }
            });
        },

        upload: function (repeatCounter) {

            var formData = new FormData();
            var input = document.querySelector('input#file_' + this.element.id);
            let divCtrlGroup = input.parentElement.parentElement.parentElement;
            var div = input.parentElement
            var deleteButton = document.querySelector('div#' + div.id + ' > a.em-deleteFile');

            formData.append('attachId', this.options.attachment_id);
            formData.append('element_id', this.options.elid);
            formData.append('fnum', this.options.fnum);
            formData.append('size', this.options.size);
            formData.append('encrypt', this.options.encrypt);
            formData.append('repeatCounter', repeatCounter);

            var file = [];
            for (var i = 0; i < input.files.length; i++) {
                file = input.files[i];
                formData.append('file[]', file);
            }

            fetch('index.php?option=com_fabrik&format=raw&task=plugin.pluginAjax&plugin=emundus_fileupload_new&method=ajax_upload', {
                body: formData,
                method: 'post'
            }).then((response) => {
                if (response.ok) {
                    return response.json();
                }
            }).then((res) => {
                if(!res) {
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

                let files = [];
                for (var j = 0; j < res.length; j++) {
                    if (res[j].ext && res[j].size && !res[j].nbMax) {
                        var inputHidden = document.querySelector('input#' + this.element.id);
                        if(inputHidden.value !== '') {
                            inputHidden.value += ',';
                        }
                        inputHidden.setAttribute("value", inputHidden.value + res[j].filename);

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

                        let file = {
                            'can_be_viewed': 1,
                            'can_be_deleted': 1,
                            'filename': res[j].filename,
                            'local_filename': res[j].local_filename,
                            'target': res[j].target,
                        };
                        files.push(file);
                    }

                    if (!res[j].ext) {
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
                        if (deleteButton) {
                            deleteButton.style.display = 'none';
                        }
                    }

                    if (!res[j].encrypt) {
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

                    if (!res[j].size) {
                        Swal.fire({
                            type: 'error',
                            title: Joomla.JText._('PLG_ELEMENT_FIELD_ERROR'),
                            text: Joomla.JText._('PLG_ELEMENT_FIELD_SIZE') + res[j].maxSize,
                            customClass: {
                                title: 'em-swal-title',
                                confirmButton: 'em-swal-confirm-button',
                                actions: "em-swal-single-action",
                            }
                        });
                        input.value = '';
                        if(deleteButton) {
                            deleteButton.style.display = 'none';
                        }                    }

                    if (res[j].nbMax) {
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
                        if(deleteButton) {
                            deleteButton.style.display = 'none';
                        }
                    }

                    if(res[j].noMoreUploads) {
                        div.querySelector('div .btn-upload').hide();
                        div.querySelector('input#' + this.element.id).hide();
                        divCtrlGroup.querySelector('.control-label').style.cursor = 'default';
                        input.hide();
                    }
                }

                this.loadFilesBlock(files);
            });
        },

        delete: function () {
            var input = document.querySelector('input#file_' + this.element.id);
            var div_parent = input.parentElement;
            var file = event.target;
            var local_filename = file.parentElement.parentElement.firstChild.innerText;
            var fileName = file.parentElement.parentElement.firstChild.href.split('/');
            fileName = fileName[fileName.length - 1];

            Swal.fire({
                title: Joomla.JText._('PLG_ELEMENT_FIELD_SURE'),
                html: Joomla.JText._('PLG_ELEMENT_FIELD_SURE_TEXT') + '<strong>' + local_filename + '</strong> ?',
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
                    const formData = new FormData();
                    formData.append('filename', fileName);
                    formData.append('attachment_id', this.options.attachment_id);
                    formData.append('fnum', this.options.fnum);
                    formData.append('element_id', this.options.elid);

                    fetch('index.php?option=com_fabrik&format=raw&task=plugin.pluginAjax&plugin=emundus_fileupload_new&method=ajax_delete', {
                        body: formData,
                        method: 'post'
                    }).then((response) => {
                        if (response.ok) {
                            return response.json();
                        }
                    }).then((res) => {
                        if (res.status) {
                            div_parent.querySelector('div.btn-upload').show();
                            input.show();

                            file.parentElement.parentElement.remove();

                            var attachmentList = div_parent.querySelectorAll('.em-fileAttachment-link').length;
                            if (attachmentList === 0) {
                                document.querySelector('div#' + this.element.id + '_attachment > .em-fileAttachmentTitle').remove();
                            }

                            var inputHidden = document.querySelector('input#' + this.element.id);
                            if(inputHidden.value !== '') {
                                var new_value = inputHidden.value.split(',');
                                if(res.upload_id) {
                                    new_value.splice(new_value.indexOf(res.upload_id), 1);
                                } else {
                                    new_value.splice(new_value.indexOf(fileName), 1);
                                }
                                inputHidden.setAttribute("value", new_value.join(','));
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

        cloned: function (c) {
            var dropzone = document.getElementById(this.element.id).previousElementSibling;
            if(dropzone) {
                dropzone.id = 'file_'+this.element.id;
            }

            var parent = document.getElementById(this.element.id).parentElement;
            if(parent) {
                parent.id = 'div_'+this.element.id;
            }

            var attachments = document.getElementById(this.element.id).nextElementSibling;
            if(attachments) {
                attachments.id = this.element.id + '_attachment';
                attachments.innerHTML = '';
            }

            document.getElementById('file_'+this.element.id).addEventListener('change', () => {
                this.upload(c);
            });

            this.parent(c);
        },

        loadFilesBlock: function(files) {
            var input = document.querySelector('input#file_' + this.element.id);
            let div = input.parentElement;
            let divAttachment = document.getElementById(this.element.id + '_attachment');

            if(files.length > 0) {
                if (!div.querySelector('.em-fileAttachmentTitle')) {
                    var attachmentTitle = document.createElement('span');
                    attachmentTitle.setAttribute("class", 'em-fileAttachmentTitle em-mt-8');
                    attachmentTitle.innerText = Joomla.JText._('PLG_ELEMENT_FILEUPLOAD_UPLOADED_FILES');
                    divAttachment.appendChild(attachmentTitle);
                }

                for (var i = 0; i < files.length; i++) {
                    var divLink = document.createElement('div');
                    divLink.setAttribute("id", this.element.id + '_attachment_link' + i);
                    divLink.setAttribute("class", 'em-fileAttachment-link');

                    divAttachment.appendChild(divLink);

                    if (files[i].can_be_viewed == 1) {
                        var link = document.createElement('a');
                        link.setAttribute("href", files[i].target);
                        link.setAttribute("target", "_blank");
                    } else {
                        var link = document.createElement('p');
                    }
                    var linkText = document.createTextNode(files[i].local_filename);

                    divLink.appendChild(link);
                    link.appendChild(linkText);

                    if (files[i].can_be_deleted == 1) {
                        var deleteButton = document.createElement('a');
                        deleteButton.setAttribute("class", 'em-pointer em-deleteFile em-ml-8');
                        deleteButton.setAttribute('value', files[i].filename);

                        var deleteIcon = document.createElement('span');
                        deleteIcon.setAttribute("class", 'material-icons-outlined');
                        deleteIcon.setAttribute("style", 'font-size: 16px');
                        deleteIcon.appendChild(document.createTextNode('clear'));
                        deleteButton.appendChild(deleteIcon);

                        divLink.appendChild(deleteButton);

                        var button = document.querySelector('#' + this.element.id + '_attachment_link' + i + ' > a.em-deleteFile');
                        button.addEventListener('click', () => this.delete());
                    }
                }
            }
        }
    });

    return window.FbEmundusFileUploadNew;
});
