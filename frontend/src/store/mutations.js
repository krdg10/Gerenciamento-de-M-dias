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


const isFetching = (state, bool) => {
  state.isFetching = bool;
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
  isFetching
};