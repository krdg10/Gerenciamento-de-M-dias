/*const isLoggedIn = state => !!state.auth.token;
const authStatus = state => state.auth.status;
const dataCompleted = state => state.dataCompleted; 
const typeUser = state => state.auth.user.role; 
*/

const displayNomeImovel = state => {
  return state.imovelNome
}

const displayListaImoveis = state => {
  return state.imoveis;

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
  displayQuantidadeDeArquivosSemImovel
};