requirejs(['fab/fabrik'], function () {
    var removedFabrikFormSkeleton = false;

    Fabrik.addEvent('fabrik.form.loaded', function (form) {
        if (!removedFabrikFormSkeleton) {
            removeFabrikFormSkeleton();
        }
    });

    window.setInterval(function() {
        if (!removedFabrikFormSkeleton && Object.entries(Fabrik.blocks).length > 0) {
            removeFabrikFormSkeleton();
        }
    }, 5000);

    function removeFabrikFormSkeleton() {
        let header = document.querySelector('.page-header');
        if(header) {
            document.querySelector('.page-header h1').style.opacity = 1;
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
});