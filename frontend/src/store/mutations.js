const created_imovel_sucess = (state, { payload }) => {
  state.imovelNome = payload.nomeImovel

};

const imoveis = (state, payloadImoveis) => {
  state.imoveis = Object.values(payloadImoveis);
}


export default {
  created_imovel_sucess,
  imoveis
};