import global from './global';
import user from './user/user';
import attachment from './attachment/attachment';
import lists from './list/list';
import file from './file/file';
import formBuilder from './formBuilder';
import campaign from './campaign';
import { createStore } from "vuex";

export default new createStore({
  modules: {
    global,
    user,
    attachment,
    file,
    lists,
    formBuilder,
    campaign
  }
});
