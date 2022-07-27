const created_imovel_sucess = (state, { payload }) => {
  state.imovelNome = payload.nomeImovel

};

const imoveis = (state, payloadImoveis) => {
  state.imoveis = Object.values(payloadImoveis);
}

const imovel = (state, payloadImovel) => {
  state.imovel = payloadImovel;
}


export default {
  created_imovel_sucess,
  imoveis,
  imovel
};