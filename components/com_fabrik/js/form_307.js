requirejs(['fab/fabrik'], function () {
    var removedFabrikFormSkeleton = false;
    var formDataChanged = false;

    Fabrik.addEvent('fabrik.form.submit.start', function (form, event) {
        event.preventDefault();
        form.result = false;
        let no_valid_elts = [];

        form.formElements.forEach(async (element) => {
            if (element.options.validations === true) {
                let is_valid = true;

                if (element.get('value') === '' || (Array.isArray(element.get('value')) && element.get('value').length === 0)) {
                    is_valid = false;
                } else {
                    if (element.element.type === 'email') {
                        is_valid = validateEmail(element.get('value'));
                    }

                    if (element.element.type === 'password') {
                        is_valid = await validatePassword(element.get('value'),element);
                    }
                }

                if (!is_valid) {
                    no_valid_elts.push(element);

                    if (element.plugin === 'fabrikcheckbox') {
                        document.querySelector('.fb_el_'+element.baseElementId + ' .fabrikErrorMessage').innerHTML = Joomla.JText._('PLEASE_CHECK_THIS_FIELD');
                    } else {
                        document.querySelector('#' + element.baseElementId).style.borderColor = 'var(--red-500)';
                        addErrorIcon(element);
                    }
                } else {
                    removeErrorIcon(element);
                    document.querySelector('.fb_el_'+element.baseElementId + ' .fabrikErrorMessage').innerHTML = '';
                    document.querySelector('#' + element.baseElementId).style.borderColor = 'inherit';
                }
            }
        });

        if(no_valid_elts.length === 0) {
            form.result = true;
        }
    });

    Fabrik.addEvent('fabrik.form.loaded', function (form) {
        if (!removedFabrikFormSkeleton) {
            removeFabrikFormSkeleton();
        }

        manageRepeatGroup(form);

        var form = document.getElementsByClassName('fabrikForm')[0];

        form.addEventListener('input', function () {
            if (!formDataChanged) {
                formDataChanged = true;
            }
        });

        var links = [];
        var checklist_items = document.querySelectorAll('.mod_emundus_checklist a');
        var logo = document.querySelectorAll('#header-a a');
        var menu_items = document.querySelectorAll('#header-b a');
        var user_items = document.querySelectorAll('#userDropdown a');
        var flow_items = document.querySelectorAll('.mod_emundus_flow___intro a');
        var footer_items = document.querySelectorAll('#g-footer a');
        var back_button_form = document.querySelectorAll('.fabrikActions .goback-btn');

        links = [...checklist_items, ...menu_items, ...user_items, ...flow_items, ...logo, ...footer_items, ...back_button_form];

        for (var i = 0, len = links.length; i < len; i++) {
            links[i].onclick = (e) => {
                if (formDataChanged) {
                    e.preventDefault();

                    Swal.fire({
                        title: Joomla.JText._('COM_EMUNDUS_FABRIK_WANT_EXIT_FORM_TITLE'),
                        text: Joomla.JText._('COM_EMUNDUS_FABRIK_WANT_EXIT_FORM_TEXT'),
                        reverseButtons: true,
                        showCloseButton: true,
                        showCancelButton: true,
                        confirmButtonText: Joomla.JText._('COM_EMUNDUS_FABRIK_WANT_EXIT_FORM_CONFIRM'),
                        cancelButtonText: Joomla.JText._('COM_EMUNDUS_FABRIK_WANT_EXIT_FORM_CANCEL'),
                        customClass: {
                            title: 'em-swal-title',
                            cancelButton: 'em-swal-cancel-button',
                            confirmButton: 'btn btn-primary save-btn sauvegarder button save_continue',
                        },
                    }).then((result) => {
                        if (result.value) {
                            let href = window.location.origin + '/index.php';
                            // If click event target is a direct link
                            if (typeof e.target.href !== 'undefined') {
                                href = e.target.href;
                            }
                            // If click event target is a child of a link
                            else {
                                e = e.target;
                                let attempt = 0;
                                do {
                                    e = e.parentNode;
                                } while (typeof e.href === 'undefined' && attempt++ < 5);

                                if (typeof e.href !== 'undefined') {
                                    href = e.href;
                                }
                            }

                            window.location.href = href;
                        }
                    });
                }
            }
        }
    });

    Fabrik.addEvent('fabrik.form.group.duplicate.end', function (form, event) {
        manageRepeatGroup(form);
    });

    Fabrik.addEvent('fabrik.form.group.delete.end', function (form, event) {
        manageRepeatGroup(form);
    });

    window.setInterval(function () {
        if (!removedFabrikFormSkeleton && Object.entries(Fabrik.blocks).length > 0) {
            removeFabrikFormSkeleton();
        }
    }, 5000);

    function removeFabrikFormSkeleton() {
        let header = document.querySelector('.page-header');
        if (header) {
            document.querySelector('.page-header h2').style.opacity = 1;
            header.classList.remove('skeleton');
        }
        let intro = document.querySelector('.em-form-intro');
        if (intro) {
            let content = document.querySelector('.em-form-intro').children;
            if (content.length > 0) {
                for (const child of content) {
                    child.style.opacity = 1;
                }
            }
            intro.classList.remove('skeleton');
        }
        let grouptitle = document.querySelectorAll('.fabrikGroup .legend');
        for (title of grouptitle) {
            title.style.opacity = 1;
        }
        grouptitle = document.querySelectorAll('.fabrikGroup h2');
        for (title of grouptitle) {
            title.style.opacity = 1;
        }
        let groupintro = document.querySelector('.groupintro');
        if (groupintro) {
            groupintro.style.opacity = 1;
        }

        let elements = document.querySelectorAll('.fabrikGroup .row-fluid');
        let elements_fields = document.querySelectorAll('.fabrikElementContainer');
        for (field of elements_fields) {
            field.style.opacity = 1;
        }
        for (elt of elements) {
            elt.style.marginTop = '0';
            elt.classList.remove('skeleton');
        }

        removedFabrikFormSkeleton = true;
    }

    function manageRepeatGroup(form) {
        setTimeout(() => {
            // ID of the group that was duplicated (ex. group686)
            let repeat_groups = form.repeatGroupMarkers;
            repeat_groups.forEach(function (repeatGroupsMarked, group) {
                if (repeatGroupsMarked !== 0) {
                    let maxRepeat = form.options.maxRepeat[group];

                    let deleteButtons = document.querySelectorAll('#group' + group + ' .fabrikGroupRepeater.pull-right');

                    if (repeatGroupsMarked > 1) {
                        deleteButtons.forEach(function (button, index) {
                            button.show();
                        })
                    } else {
                        deleteButtons.forEach(function (button, index) {
                            button.hide();
                        })
                    }

                    let addButtons = document.querySelectorAll('#group' + group + ' .fabrikGroupRepeater .addGroup');

                    if (maxRepeat !== 0 && repeatGroupsMarked >= maxRepeat) {
                        addButtons.forEach(function (button, index) {
                            button.hide();
                        })
                    } else {
                        addButtons.forEach(function (button, index) {
                            button.show();
                        })
                    }
                }
            });
        }, 100)
    }

    function validateEmail(value) {
        var validRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;

        return value.match(validRegex);
    }

    function validatePassword(password,element) {
        let is_valid = true;

        return fetch('index.php?option=com_emundus&controller=user&task=getpasswordsecurity')
            .then((response) => {
                if (response.ok) {
                    return response.json();
                }
            }).then((result) => {
            const rules = result.rules;

            if (rules.minimum_length && password.length < rules.minimum_length) {
                is_valid = false;
            }

            if (rules.minimum_integers) {
                let regex = '[0-9]{'+rules.minimum_integers+'}';
                regex  = new RegExp(regex);
                if(password.match(regex) === null) {
                    is_valid = false;
                }
            }

            if(rules.minimum_lowercase) {
                let regex = '[a-z]{'+rules.minimum_lowercase+'}';
                regex  = new RegExp(regex);
                if(password.match(regex) === null) {
                    is_valid = false;
                }
            }

            if(rules.minimum_uppercase) {
                let regex = '[A-Z]{'+rules.minimum_uppercase+'}';
                regex  = new RegExp(regex);
                if(password.match(regex) === null) {
                    is_valid = false;
                }
            }

            if(rules.minimum_symbols) {
                let regex = '[?&@*+%=.,€£)(!\-_`:]{'+rules.minimum_symbols+'}';
                regex  = new RegExp(regex);
                if(password.match(regex) === null) {
                    is_valid = false;
                }
            }

            return is_valid;
        }).catch((error) => {
            return is_valid;
        });
    }

    function addErrorIcon(element) {
        const errorIcon = document.createElement('span');
        errorIcon.classList.add('material-icons');
        errorIcon.classList.add('registration-error-icon');
        errorIcon.innerHTML = 'error';
        errorIcon.style.color = 'var(--red-500)';
        errorIcon.style.position = 'absolute';
        if (element.element.type === 'password') {
            errorIcon.style.right = '32px';
        } else {
            errorIcon.style.right = '12px';
        }
        errorIcon.style.top = '10px';

        const eltHtml =  document.querySelector('.fb_el_'+element.baseElementId + ' .fabrikElement');
        if(eltHtml) {
            eltHtml.append(errorIcon);
        }
    }

    function removeErrorIcon(element) {
        const eltHtml =  document.querySelector('.fb_el_'+element.baseElementId + ' .fabrikElement .registration-error-icon');
        if(eltHtml) {
            eltHtml.remove();
        }
    }
});
