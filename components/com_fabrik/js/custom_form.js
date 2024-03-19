requirejs(['fab/fabrik'], function () {
    var removedFabrikFormSkeleton = false;
    var formDataChanged = false;
    var js_rules = [];
    var table_name = '';

    let check_condition = arr => arr.every(v => v === true);
    var operators = {
        '=': function(a, b, plugin) { if(!Array.isArray(a)) { return a == b; } else { return a.includes(b); } },
        '!=': function(a, b, plugin) { if(!Array.isArray(a)) { return a != b; } else { return !a.includes(b); } },
        // ...
    };

    Fabrik.addEvent('fabrik.form.loaded', function (form) {
        table_name = form.options.primaryKey.split('___')[0];

        manageRepeatGroup(form);

        var formBlock = document.getElementsByClassName('fabrikForm')[0];

        formBlock.addEventListener('input', function () {
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
                            if (e.srcElement.classList.contains('goback-btn')) {
                                window.history.back();
                            }

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
                } else {
                    if (e.srcElement.classList.contains('goback-btn')) {
                        if (window.history.length > 1) {
                            window.history.back();
                        } else {
                            window.close();
                        }
                    }
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

    Fabrik.addEvent('fabrik.form.elements.added', function (form, event) {
        setTimeout(() => {
            fetch('/index.php?option=com_emundus&controller=form&task=getjsconditions&form_id=' + form.id).then(response => response.json()).then(data => {
                if (data.status) {
                    js_rules = data.data.conditions;

                    form.elements.forEach(function (element) {
                        manageRules(form, element, false);

                        var $el = jQuery(element.element);
                        $el.on(element.getChangeEvent(), function (e) {
                            manageRules(form, element);
                        });
                    });
                }

                if (!removedFabrikFormSkeleton) {
                    removeFabrikFormSkeleton();
                }
            });
        },500);
    });

    window.setInterval(function () {
        if (!removedFabrikFormSkeleton && Object.entries(Fabrik.blocks).length > 0) {
            removeFabrikFormSkeleton();
        }
    }, 5000);

    function removeFabrikFormSkeleton() {
        let header = document.querySelector('.page-header');
        if (header) {
            if (header.querySelector('h2')) {
                document.querySelector('.page-header h2').style.opacity = 1;
            }
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
        grouptitle = document.querySelectorAll('.fabrikGroup h2, .fabrikGroup h3');
        for (title of grouptitle) {
            title.style.opacity = 1;
        }
        let groupintros = document.querySelectorAll('.groupintro');
        if (groupintros) {
            groupintros.forEach((groupintro) => {
                groupintro.style.opacity = 1;
            });
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
                    let minRepeat = form.options.minRepeat[group];
                    let maxRepeat = form.options.maxRepeat[group];

                    let deleteButtons = document.querySelectorAll('#group' + group + ' .fabrikGroupRepeater.pull-right');

                    if (repeatGroupsMarked > 1) {
                        deleteButtons.forEach(function (button) {
                            button.show();
                        });
                    } else if (minRepeat > 0) {
                        deleteButtons.forEach(function (button) {
                            button.hide();
                        });
                    }

                    let addButtons = document.querySelectorAll('#group' + group + ' .fabrikGroupRepeater .addGroup');

                    if (maxRepeat !== 0 && repeatGroupsMarked >= maxRepeat) {
                        addButtons.forEach(function (button, index) {
                            button.hide();
                        })
                    } else {
                        if (addButtons.length > 1) {
                            addButtons.forEach(function (button, index) {
                                if ((index + 1) < addButtons.length) {
                                    button.hide();
                                } else {
                                    button.style.display = 'flex';
                                }
                            })
                        } else {
                            addButtons.forEach(function (button, index) {
                                button.style.display = 'flex';
                            })
                        }
                    }
                }
            });
        }, 100)
    }

    function manageRules(form, element, clear = true) {
        let elt_name = element.origId ? element.origId.split('___')[1] : element.baseElementId.split('___')[1];

        let elt_rules = [];
        js_rules.forEach((js_rule) => {
            js_rule.conditions.forEach((condition) => {
                if (condition.field == elt_name) {
                    elt_rules.push(js_rule);
                }
            });
        });

        if (elt_rules.length > 0) {
            elt_rules.forEach((rule) => {
                let condition_state = [];

                rule.conditions.forEach((condition) => {
                    form.elements.forEach((elt) => {
                        let name = elt.origId ? elt.origId.split('___')[1] : elt.baseElementId.split('___')[1];
                        if (name == condition.field) {
                            if(operators[condition.state](elt.get('value'), condition.values, elt.plugin)) {
                                condition_state.push(true);
                            } else if(rule.group == 'AND') {
                                condition_state.push(false);
                            }
                        }
                    });
                });

                if (condition_state.length > 0 && check_condition(condition_state)) {
                    rule.actions.forEach((action) => {

                        let fields = action.fields.split(',');

                        form.elements.forEach((elt) => {
                            let name = elt.origId ? elt.origId.split('___')[1] : elt.baseElementId.split('___')[1];
                            if(fields.includes(name)) {
                                form.doElementFX('element_'+elt.strElement, action.action, elt);

                                if(action.action == 'hide') {
                                    if(clear) {
                                        elt.clear();
                                    }
                                    let event = new Event(elt.getChangeEvent());
                                    elt.element.dispatchEvent(event);
                                }
                            }
                        });
                    });
                } else {
                    let opposite_action = 'hide';

                    rule.actions.forEach((action) => {
                        switch (action.action) {
                            case 'show':
                                opposite_action = 'hide';
                                break;
                            case 'hide':
                                opposite_action = 'show';
                                break;
                        }

                        let fields = action.fields.split(',');

                        form.elements.forEach((elt) => {
                            let name = elt.origId ? elt.origId.split('___')[1] : elt.baseElementId.split('___')[1];
                            if(fields.includes(name)) {
                                form.doElementFX('element_'+elt.strElement, opposite_action, elt);

                                if(opposite_action == 'hide') {
                                    if(clear) {
                                        elt.clear();
                                    }
                                    let event = new Event(elt.getChangeEvent());
                                    elt.element.dispatchEvent(event);
                                }
                            }
                        });
                    });
                }
            });
        }
    }
});
