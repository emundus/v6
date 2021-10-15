import user from './user/user'
import attachment from './attachment/attachment'
import file from './file/file'
import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

export default new Vuex.Store({
  modules: {
    user,
    attachment,
    file
  }
})