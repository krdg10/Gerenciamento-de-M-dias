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

const displayImovel = state => {
  return state.imovel;

}

export default {
  displayNomeImovel,
  displayListaImoveis,
  displayImovel
};