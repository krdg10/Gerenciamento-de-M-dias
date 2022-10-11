import Vuex from 'vuex';
import { createApp } from 'vue'
import App from '../App.vue'
import actions from './actions';
import getters from './getters';
import mutations from './mutations';

const app = createApp(App)
app.use(Vuex)

export const store = new Vuex.Store({
  state: {
    imoveis: [],
    imovel: {},
    arquivos: [],
    quantidadeDeImoveisAtivos: 0,
    quantidadeDeImoveisInativos: 0,
    quantidadeDeArquivosAtivos: 0,
    quantidadeDeArquivosInativos: 0,
    quantidadeDeArquivosSemImovel: 0,
    isFetching: { status: false, message: '' },
  },

  actions,
  getters,
  mutations,
});