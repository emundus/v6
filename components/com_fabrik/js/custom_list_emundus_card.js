requirejs(['fab/fabrik'], function () {
    var removedFabrikFormSkeleton = false;

    Fabrik.addEvent('fabrik.list.loaded', function (form) {
        if (!removedFabrikFormSkeleton) {
            removeFabrikFormSkeleton();
        }
    });

    window.setInterval(function() {
        if (!removedFabrikFormSkeleton && Object.entries(Fabrik.blocks).length > 0) {
            removeFabrikFormSkeleton();
        }
    }, 3000);

    function removeFabrikFormSkeleton() {
        // Load skeleton
        let header = document.querySelector('.page-header');
        if(header) {
            document.querySelector('.page-header h2').style.opacity = 1;
            document.querySelector('.page-header .em-list-intro').style.opacity = 1;
            document.querySelector('.page-header').classList.remove('skeleton');
        }

        let switch_button_views = document.querySelector('.fabrik-switch-view-buttons');
        if (switch_button_views) {
            switch_button_views.style.opacity = 1;
            switch_button_views.classList.remove('skeleton');
        }

        let filters = document.querySelector('.filtertable');
        if (filters){
            filters.style.opacity = 1;
            document.querySelector('.fabrikFiltersBlock').classList.remove('skeleton');
        }

        let submit_button = document.querySelector('.fabrik_filter_submit');
        if(submit_button){
            submit_button.style.opacity = 1;
            document.querySelector('#fabrikFiltersButtonSubmit').classList.remove('skeleton');
        }

        let nav = document.querySelector('.fabrikNav');
        if(nav){
            document.querySelector('.fabrikNav div').style.opacity = 1;
            nav.classList.remove('skeleton');
        }

        let cards = document.querySelectorAll('.fabrik_row');
        let elts_p = document.querySelectorAll('.fabrik_row p');
        let elts_div = document.querySelectorAll('.fabrik_row div');
        for (elt_div of elts_div){
            elt_div.style.opacity = 1;
        }
        for (elt_p of elts_p){
            elt_p.style.opacity = 1;
        }
        for (card of cards){
            card.classList.remove('skeleton');
        }

        removedFabrikFormSkeleton = true;
    }
});
