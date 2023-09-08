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
