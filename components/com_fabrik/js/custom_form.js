requirejs(['fab/fabrik'], function () {
    var removedFabrikFormSkeleton = false;
    var formDataChanged = false;

    var form = document.getElementsByTagName('form')[0];

    form.addEventListener("input", function () {
        console.log("Form has changed!");
    });

    Fabrik.addEvent('fabrik.form.loaded', function (form) {
        if (!removedFabrikFormSkeleton) {
            removeFabrikFormSkeleton();
        }

        manageRepeatGroup(form);

        var form = document.getElementsByClassName('fabrikForm')[0];

        form.addEventListener('input', function () {
            if(!formDataChanged) {
                formDataChanged = true;
            }
        });

        var links = [];
        var checklist_items = document.querySelectorAll('.mod_emundus_checklist a');
        var menu_items = document.querySelectorAll('#header-b a');
        var user_items = document.querySelectorAll('#userDropdown a');
        var flow_items = document.querySelectorAll('.mod_emundus_flow___intro a');

        links = [...checklist_items, ...menu_items, ...user_items, ...flow_items];

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
                            window.location.href = e.target.href;
                        }
                    });
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

    window.setInterval(function() {
        if (!removedFabrikFormSkeleton && Object.entries(Fabrik.blocks).length > 0) {
            removeFabrikFormSkeleton();
        }
    }, 5000);

    function removeFabrikFormSkeleton() {
        let header = document.querySelector('.page-header');
        if(header) {
            document.querySelector('.page-header h2').style.opacity = 1;
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
        grouptitle = document.querySelectorAll('.fabrikGroup h2');
        for (title of grouptitle){
            title.style.opacity = 1;
        }
        let groupintro = document.querySelector('.groupintro');
        if (groupintro) {
            groupintro.style.opacity = 1;
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
        },100)
    }
});
