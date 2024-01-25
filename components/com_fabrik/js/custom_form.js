requirejs(['fab/fabrik'], function () {
    var removedFabrikFormSkeleton = false;
    var formDataChanged = false;

    Fabrik.addEvent('fabrik.form.loaded', function (form) {
        setCookie('fabrik_form_session', 'true', 15);

        setInterval(() => {
            let active_form_session = getCookie('fabrik_form_session');
            if(!active_form_session) {
                alert(Joomla.JText._('COM_EMUNDUS_FABRIK_SESSION_EXPIRED'));
                setTimeout(() => {
                    window.location.href = window.location.origin + '/';
                }, 2000);
            }
        }, 10000);

        if (!removedFabrikFormSkeleton) {
            removeFabrikFormSkeleton();
        }

        manageRepeatGroup(form);

        var formElt = document.getElementsByClassName('fabrikForm')[0];

        formElt.addEventListener('input', function () {
            if(!formDataChanged) {
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

        for(var i = 0, len = links.length; i < len; i++) {
            links[i].onclick = (e) => {
                if(formDataChanged) {
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
                        if(result.value)
                        {
                            clearFormSession(form.id);
                            if(e.srcElement.classList.contains('goback-btn')) {
                                window.history.back();
                            }

                            let href = window.location.origin+'/index.php';
                            // If click event target is a direct link
                            if(typeof e.target.href !== 'undefined')
                            {
                                href = e.target.href;
                            }
                            // If click event target is a child of a link
                            else
                            {
                                e = e.target;
                                let attempt = 0;
                                do {
                                    e = e.parentNode;
                                } while(typeof e.href === 'undefined' && attempt++ < 5);

                                if(typeof e.href !== 'undefined') {
                                    href = e.href;
                                }
                            }

                            window.location.href = href;
                        }
                    });
                } else {
                    clearFormSession(form.id);
                    if(e.srcElement.classList.contains('goback-btn')) {
                        if(window.history.length > 1) {
                            window.history.back();
                        } else {
                            window.close();
                        }
                    }
                }
            }
        }
    });

    Fabrik.addEvent('fabrik.form.group.duplicate.end', function(form, event) {
        manageRepeatGroup(form);
    });

    Fabrik.addEvent('fabrik.form.group.delete.end', function(form, event) {
        manageRepeatGroup(form);
    });

    Fabrik.addEvent('fabrik.form.elements.added', function (form) {
        Object.entries(form.elements).forEach(([key, element]) => {
            let event = 'blur';
            if (['fabrikradiobutton', 'databasejoin'].includes(element.plugin)) {
                event = 'change';
            }
            element.element.addEventListener(event, (e) => saveDatas(element, e))
        });
    });

    window.setInterval(function() {
        if (!removedFabrikFormSkeleton && Object.entries(Fabrik.blocks).length > 0) {
            removeFabrikFormSkeleton();
        }
    }, 5000);

    function removeFabrikFormSkeleton() {
        let header = document.querySelector('.page-header');
        if(header) {
            if(header.querySelector('h2')) {
                document.querySelector('.page-header h2').style.opacity = 1;
            }
            header.classList.remove('skeleton');
        }
        let intro = document.querySelector('.em-form-intro');
        if(intro) {
            let content = document.querySelector('.em-form-intro').children;
            if(content.length > 0) {
                for (const child of content) {
                    child.style.opacity = 1;
                }
            }
            intro.classList.remove('skeleton');
        }
        let grouptitle = document.querySelectorAll('.fabrikGroup .legend');
        for (title of grouptitle){
            title.style.opacity = 1;
        }
        grouptitle = document.querySelectorAll('.fabrikGroup h2, .fabrikGroup h3');
        for (title of grouptitle){
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
        for (field of elements_fields){
            field.style.opacity = 1;
        }
        for (elt of elements){
            elt.style.marginTop = '0';
            elt.classList.remove('skeleton');
        }

        removedFabrikFormSkeleton = true;
    }

    function manageRepeatGroup(form)
    {
        setTimeout(() => {
            // ID of the group that was duplicated (ex. group686)
            let repeat_groups = form.repeatGroupMarkers;
            repeat_groups.forEach(function (repeatGroupsMarked, group) {
                if(repeatGroupsMarked !== 0) {
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
                        if(addButtons.length > 1) {
                        addButtons.forEach(function (button, index) {
                                if((index + 1) < addButtons.length) {
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
        },100)
    }

    function saveDatas(element, event) {
        let name = element.baseElementId;
        let value = element.get('value');

        let formData = new FormData();
        formData.append('element', name);
        formData.append('value', value);
        formData.append('form_id', element.form.id);

        fetch('/index.php?option=com_emundus&controller=application&task=saveformsession', {
            method: 'POST',
            credentials: 'same-origin',
            body: formData,
        }).then((response) => {
            return response.json();
        }).then((data) => {
            if (data.success) {
                setCookie('fabrik_form_session', 'true', 15);
            }
        }).catch((error) => {
            console.error('Error:', error);
        });
    }

    function clearFormSession(form_id) {
        let formData = new FormData();
        formData.append('form_id', form_id);

        fetch('/index.php?option=com_emundus&controller=application&task=clearformsession', {
            method: 'POST',
            credentials: 'same-origin',
            body: formData,
        }).then((response) => {
            return response.json();
        }).then((data) => {
            if (data.success) {
                document.cookie = "fabrik_form_session=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            }
        }).catch((error) => {
            console.error('Error:', error);
        });
    }

    function setCookie(cname, cvalue, minutes) {
        const d = new Date();
        d.setTime(d.getTime() + (minutes*60*1000));
        let expires = "expires="+ d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }

    function getCookie(cname) {
        let name = cname + "=";
        let decodedCookie = decodeURIComponent(document.cookie);
        let ca = decodedCookie.split(';');
        for(let i = 0; i <ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }
});
