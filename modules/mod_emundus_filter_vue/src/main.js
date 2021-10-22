import Vue from 'vue'
import App from './App.vue'
import store from './store'

Vue.config.devtools = true;

if (document.getElementById("em-filter-builder-vue")) {
    new Vue({
        el: '#em-filter-builder-vue',
        store,
        render(h) {
            return h(App)
        }
    })
}