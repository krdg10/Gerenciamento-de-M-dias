import axios from 'axios';
//import * as Cookies from 'js-cookie'

//  const imoveis = 'http://localhost:8000/imoveis'; 
const imovelUrl = 'http://localhost:8000/imovel/';
const arquivoUrl = 'http://localhost:8000/arquivo/';


//https://stackoverflow.com/questions/52630866/vuex-actions-that-do-not-need-to-commit-a-mutation
const createImovel = async (_, imovel) => {
    return await axios({ url: imovelUrl + 'novo', data: imovel, method: 'POST' })
        .then(response => {
            return response
        }).catch(error => {
            console.log(error)
        })
};

const loadArquivos = async ({ commit }) => {
    return await axios({ url: arquivoUrl + 'buscarTodos', method: 'GET' })
        .then(response => {
            const payloadArquivos = response.data;
            commit('arquivos', payloadArquivos);
            return response.data
        }).catch(error => {
            console.log(error)
        })
};

const loadImoveis = async ({ commit }) => {
    return await axios({ url: imovelUrl + 'buscarTodos', method: 'GET' })
        .then(response => {
            const payloadImoveis = response.data;
            commit('imoveis', payloadImoveis);
            return response.data
        }).catch(error => {
            console.log(error)
        })
};

const buscaImovel = async ({ commit }, busca) => {
    return await axios({ url: imovelUrl + 'busca' + '/' + busca, method: 'GET' })
        .then(response => {
            const payloadImoveis = response.data;
            commit('imoveis', payloadImoveis);
            return response.data
        }).catch(error => {
            console.log(error)
        })
};

const buscaArquivo = async ({ commit }, busca) => {
    return await axios({ url: arquivoUrl + 'busca' + '/' + busca, method: 'GET' })
        .then(response => {
            const payloadArquivos = response.data;
            commit('arquivos', payloadArquivos);
            return response.data
        }).catch(error => {
            console.log(error)
        })
};

const updateImovel = async ({ commit }, imovel) => {
    return await axios({ url: imovelUrl + 'editar' + '/' + imovel.id, data: imovel, method: 'PUT' })
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
    return await axios({ url: imovelUrl + 'deletarImovel' + '/' + id, method: 'PUT' })
        .then(response => {
            return response
        }).catch(error => {
            console.log(error)
        })
};

const apagarArquivo = async (_, id) => {
    return await axios({ url: arquivoUrl + 'deletarArquivo' + '/' + id, method: 'PUT' })
        .then(response => {
            return response
        }).catch(error => {
            console.log(error)
        })
};

const alterarTag = async ({ commit }, payload) => {
    return await axios({ url: imovelUrl + 'alterarTag' + '/' + payload.id, data: payload, method: 'PUT' })
        .then(response => {
            commit('alterTag', payload);
            return response
        }).catch(error => {
            console.log(error)
        })
};

const updateArquivo = async (_, payload) => {
    console.log(payload)
    return await axios({ url: arquivoUrl + 'editar' + '/' + payload.id, data: payload, method: 'PUT' })
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
    apagarImovel,
    alterarTag,
    loadArquivos,
    buscaArquivo,
    apagarArquivo,
    updateArquivo
};