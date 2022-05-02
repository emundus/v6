import global from './global';
import user from './user/user';
import attachment from './attachment/attachment';
import lists from './list/list';
import file from './file/file';
import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

export default new Vuex.Store({
  modules: {
    global,
    user,
    attachment,
    file,
    lists
  }
});
