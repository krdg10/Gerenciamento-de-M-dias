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

export default {
  displayNomeImovel,
  displayListaImoveis
};