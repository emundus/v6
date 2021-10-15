import Vue from 'vue';
import Router from 'vue-router'
import AddEmail from './views/addEmail.vue';
import List from './views/list.vue';

Vue.use(Router);

const routes = [
  {
    path: '/',
    name: 'emails',
    component: List,
    props: (route) => ({
      datas: route.params
    })
  },
  {
    path: '/edit-email/:email',
    name: 'edit-email',
    // route level code-splitting
    // this generates a separate chunk (about.[hash].js) for this route
    // which is lazy-loaded when the route is visited.
    component: AddEmail,
  },
]

const router = new Router({
  mode: 'history',
  routes
})

export default router
