import axios from 'axios';
//import * as Cookies from 'js-cookie'

//  const imoveis = 'http://localhost:8000/imoveis'; 
const imovelUrl = 'http://localhost:8000/imovel';
const buscaUrl = 'http://localhost:8000/busca';
const deleteUrl = 'http://localhost:8000/deleteImovel';

//https://stackoverflow.com/questions/52630866/vuex-actions-that-do-not-need-to-commit-a-mutation
const createImovel = async (_, imovel) => {
    return await axios({ url: imovelUrl, data: imovel, method: 'POST' })
        .then(response => {
            return response
        }).catch(error => {
            console.log(error)
        })
};

const loadImoveis = async ({ commit }) => {
    return await axios({ url: imovelUrl, method: 'GET' })
        .then(response => {
            const payloadImoveis = response.data;
            commit('imoveis', payloadImoveis);
            return response.data
        }).catch(error => {
            console.log(error)
        })
};

const buscaImovel = async ({ commit }, busca) => {
    return await axios({ url: buscaUrl + '/' + busca, method: 'GET' })
        .then(response => {
            const payloadImoveis = response.data;
            commit('imoveis', payloadImoveis);
            return response.data
        }).catch(error => {
            console.log(error)
        })
};

const updateImovel = async ({ commit }, imovel) => {
    return await axios({ url: imovelUrl + '/' + imovel.id, data: imovel, method: 'PUT' })
        .then(response => {
            const payload = imovel;
            commit('imovel', payload)
            return response
        }).catch(error => {
            console.log(error)
        })
};

const apagarImovel = async (_, id) => {
    console.log(id);
    return await axios({ url: deleteUrl + '/' + id, method: 'PUT' })
        .then(response => {
            return response
        }).catch(error => {
            console.log(error)
        })
};

export default {
    createImovel,
    loadImoveis,
    buscaImovel,
    updateImovel,
    apagarImovel
};