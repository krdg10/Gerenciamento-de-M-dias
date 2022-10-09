const displayNomeImovel = state => {
  return state.imovelNome
}

const displayListaImoveis = (state) => {
  return state.imoveis;

  /*if (!tags) {
    return state.imoveis;
  }
  
  let imoveis = state.imoveis;
  if (tags.filterImportant == 1) {
    imoveis = imoveis.filter((teste) => { return teste.importante == 1 })
  }
  if (tags.filterFav == 1) {
    imoveis = imoveis.filter((teste) => { return teste.favorito == 1 })
  }
  if (tags.filterUrgent == 1) {
    imoveis = imoveis.filter((teste) => { return teste.urgente == 1 })
  }
  return imoveis;*/
}

const displayListaImoveisAtivos = state => {
  let imoveis = state.imoveis;
  let imoveisAtivos = imoveis.filter((imoveisAtivos) => { return imoveisAtivos.ativo == 'A' })
  return imoveisAtivos;
}

const displayListaArquivos = state => {
  return state.arquivos;

}

const displayImovel = state => {
  return state.imovel;
}

const displayImovelById = (state) => (imovel_id) => {
  let imoveis = state.imoveis;
  let imoveisById = imoveis.filter((imoveisById) => { return imoveisById.id == imovel_id })
  return imoveisById[0]
}

const displayQuantidadeDeImoveisAtivos = state => {
  return state.quantidadeDeImoveisAtivos;
}

const displayQuantidadeDeImoveisInativos = state => {
  return state.quantidadeDeImoveisInativos;
}

const displayQuantidadeDeArquivossAtivos = state => {
  return state.quantidadeDeArquivosAtivos;
}

const displayQuantidadeDeArquivosInativos = state => {
  return state.quantidadeDeArquivosInativos;
}

const displayQuantidadeDeArquivosSemImovel = state => {
  return state.quantidadeDeArquivosSemImovel;
}

export default {
  displayNomeImovel,
  displayListaImoveis,
  displayImovel,
  displayListaArquivos,
  displayImovelById,
  displayQuantidadeDeImoveisAtivos,
  displayQuantidadeDeImoveisInativos,
  displayQuantidadeDeArquivossAtivos,
  displayQuantidadeDeArquivosInativos,
  displayQuantidadeDeArquivosSemImovel,
  displayListaImoveisAtivos
};