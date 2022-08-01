
const imoveis = (state, payloadImoveis) => {
  state.imoveis = Object.values(payloadImoveis);
}

const imovel = (state, payloadImovel) => {
  state.imovel = payloadImovel;
}


export default {
  imoveis,
  imovel
};