import Vue from 'vue';
import VModal from 'vue-js-modal';
import store from "./store";
import App from './App.vue';
import translate from './mixins/translate.js';

let mountApp = false;
let elementId = "";
let data = {};
let componentName = "";

if (document.getElementById("em-application-attachment")) {
    const element = document.getElementById("em-application-attachment");
    Array.prototype.slice.call(element.attributes).forEach(function (attr) {
        data[attr.name] = attr.value;
    });

    componentName = "attachments";
    elementId = "#em-application-attachment";
    mountApp = true;
}

if (mountApp) {
    // destroy vue instance when changing page
    const items = document.querySelectorAll('#em-appli-menu .list-group-item');
    items.forEach(function (item) {
        item.addEventListener('click', function () {
            vue.$destroy();
        });
    });


    // add eventlistener on changeFile
    function changeFile(e) {
        document.querySelector('#em-assoc-files .panel-body').empty();
        const checkedEm = document.querySelector('.em-check:checked');
        // uncheck element
        if (checkedEm) {
            checkedEm.checked = false;
        }

        // check element that have id equals to e.detail.fnum.fnum + "_check"
        const check = document.getElementById(e.detail.fnum.fnum + "_check");
        if (check) {
            check.checked = true;
        }

        // update href fnum param
        items.forEach(function (item) {
            item.setAttribute('href', item.getAttribute('href').replace(/fnum=\d+/, 'fnum=' + e.detail.fnum.fnum));
        });


        openFiles(e.detail.fnum, "attachment", true);
        // display em-container-menuaction-nav
        setTimeout(() => {
            document.querySelector('.em-container-menuaction-nav').style.display = "block";
        }, 500);
    }

    window.addEventListener('changeFile', changeFile);

    Vue.config.productionTip = false;
    Vue.use(store);
    Vue.use(VModal);
    Vue.mixin(translate);

    const vue = new Vue({
        el: elementId,
        store,
        render(h) {
            return h(
                App, {
                props: {
                    componentName: componentName,
                    data: data
                },
            }
            );
        },
    });

    // on vue destroy, remove event listener
    vue.$on('hook:beforeDestroy', function () {
        window.removeEventListener('changeFile', changeFile);
    });
}