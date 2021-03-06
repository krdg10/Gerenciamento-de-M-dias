import axios from 'axios';
//import * as Cookies from 'js-cookie'

//  const imoveis = 'http://localhost:8000/imoveis'; 
const imovelUrl = 'http://localhost:8000/imovel';

const createImovel = async ({ commit }, imovel) => {

    return await axios({ url: imovelUrl, data: imovel, method: 'POST' })
        .then(response => {
            const payload = {
                nomeImovel: imovel.nome
            }
            commit('created_imovel_sucess', { payload })

            return response
        }).catch(error => {
            console.log(error)
        })
};


export default {
    createImovel
};