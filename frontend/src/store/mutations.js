import VueCookies from 'vue-cookies'

const imoveis = (state, payloadImoveis) => {
  state.imoveis = Object.values(payloadImoveis);
}

const arquivos = (state, payloadArquivos) => {
  state.arquivos = Object.values(payloadArquivos);
}

const imovel = (state, payloadImovel) => {
  state.imovel = payloadImovel;
}

const alterTag = (state, payload) => {
  let index = state.imoveis.findIndex(x => x.id == payload.imovelId);
  if (payload.type == 'urgente') {
    if (state.imoveis[index].urgente == 0) {
      state.imoveis[index].urgente = 1;
    }
    else {
      state.imoveis[index].urgente = 0;
    }
  }
  else if (payload.type == 'favorito') {
    if (state.imoveis[index].favorito == 0) {
      state.imoveis[index].favorito = 1;
    }
    else {
      state.imoveis[index].favorito = 0;
    }
  }
  else if (payload.type == 'importante') {
    if (state.imoveis[index].importante == 0) {
      state.imoveis[index].importante = 1;
    }
    else {
      state.imoveis[index].importante = 0;
    }
  }
}

const quantidadeImoveisAtivos = (state, payloadImoveisAtivos) => {
  state.quantidadeDeImoveisAtivos = payloadImoveisAtivos;
}

const quantidadeImoveisInativos = (state, payloadImoveisInativos) => {
  state.quantidadeDeImoveisInativos = payloadImoveisInativos;
}

const quantidadeArquivosAtivos = (state, payloadArquivosAtivos) => {
  state.quantidadeDeArquivosAtivos = payloadArquivosAtivos;
}

const quantidadeArquivosInativos = (state, payloadArquivosInativos) => {
  state.quantidadeDeArquivosInativos = payloadArquivosInativos;
}

const quantidadeArquivosSemImovel = (state, payloadArquivosSemImovel) => {
  state.quantidadeDeArquivosSemImovel = payloadArquivosSemImovel;
}


const isFetching = (state, object) => {
  state.isFetching.status = object.status;
  state.isFetching.message = object.message;
}

const isLoggedIn = (state, object) => {
  state.login.isLoggedIn = true;
  state.login.type = object.type;
  VueCookies.set('isLoggedIn', 'true')
  VueCookies.set('type', object.type)
  VueCookies.set('token', object.token)
}

const isLoggedOff = (state) => {
  state.login.isLoggedIn = false;
  state.login.type = '';
  VueCookies.set('isLoggedIn', 'false')
  VueCookies.set('type', 'false')
  VueCookies.set('token', 'false')
}

export default {
  imoveis,
  imovel,
  alterTag,
  arquivos,
  quantidadeImoveisAtivos,
  quantidadeImoveisInativos,
  quantidadeArquivosAtivos,
  quantidadeArquivosInativos,
  quantidadeArquivosSemImovel,
  isFetching,
  isLoggedIn,
  isLoggedOff
};