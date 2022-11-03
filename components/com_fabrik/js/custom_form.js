requirejs(['fab/fabrik'], function () {
    Fabrik.addEvent('fabrik.form.loaded', function (form) {
        jQuery('#em-dimmer').remove();

        let header = document.querySelector('.page-header');
        if(header) {
            document.querySelector('.page-header h1').style.opacity = 1;
            header.classList.remove('skeleton');
        }
        let grouptitle = document.querySelectorAll('.fabrikGroup .legend');
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
            elt.classList.remove('skeleton');
        }
    });
});
