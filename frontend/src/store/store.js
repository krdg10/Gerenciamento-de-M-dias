import Vuex from 'vuex';
import { createApp } from 'vue'
import App from '../App.vue'
//import createPersistedState from 'vuex-persistedstate'
//import * as Cookies from 'js-cookie'

import actions from './actions';
import getters from './getters';
import mutations from './mutations';


const app = createApp(App)
app.use(Vuex)

export const store = new Vuex.Store({
  state: {
    imoveis: [],
    imovel: {},
    arquivos: []
  },


  actions,
  getters,
  mutations,
});