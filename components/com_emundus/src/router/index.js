import Vue from 'vue';
import Router from 'vue-router';
import App from '../App.vue';
import Attachments from '../views/Attachments.vue';

Vue.use(Router);

function addPropsFromRoute(route) {
    return {
        ...route.params,
    };
}

export default new Router({
    routes: [
      {
        path: '/',
        name: 'app',
        component: App,
      },
      {
        path: '/attachments',
        name: 'attachments',
        component: Attachments,
        props: route => addPropsFromRoute(route),
      },
      // {
      //   path: '/attachments/:fnum',
      //   name: 'attachments',
      //   component: Attachments,
      //   props: route => addPropsFromRoute(route),
      // },
    ],
});
  