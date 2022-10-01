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
    return await axios({ url: arquivoUrl + 'buscarTodosValidos', method: 'GET' })
        .then(response => {
            const payloadArquivos = response.data;
            commit('arquivos', payloadArquivos);
            return response.data
        }).catch(error => {
            console.log(error)
        })
};

const loadArquivosSemImoveis = async ({ commit }) => {
    return await axios({ url: arquivoUrl + 'ativosSemImovel', method: 'GET' })
        .then(response => {
            const payloadArquivos = response.data;
            console.log(payloadArquivos)
            commit('arquivos', payloadArquivos);
            return response.data
        }).catch(error => {
            console.log(error)
        })
};

const loadImoveis = async ({ commit }) => {
    return await axios({ url: imovelUrl + 'buscarTodosValidos', method: 'GET' })
        .then(response => {
            const payloadImoveis = response.data;
            commit('imoveis', payloadImoveis);
            return response.data
        }).catch(error => {
            console.log(error)
        })
};

const loadImoveisValidosEInvalidos = async ({ commit }) => {
    return await axios({ url: imovelUrl + 'buscarTodosValidosEInvalidos', method: 'GET' })
        .then(response => {
            const payloadImoveis = response.data;
            commit('imoveis', payloadImoveis);
            return response.data
        }).catch(error => {
            console.log(error)
        })
};


const buscaImovel = async ({ commit }, busca) => {
    return await axios({ url: imovelUrl + 'busca' + '/' + busca.keywords + '/' + busca.status + '/' + busca.offset + '/' + busca.limit, method: 'GET' })
        .then(response => {
            const payloadImoveis = response.data.resultado;
            commit('imoveis', payloadImoveis);
            return response.data
        }).catch(error => {
            console.log(error)
        })
};

const buscaArquivo = async ({ commit }, busca) => {
    let url;
    if (busca.tipo == 'nome') {
        url = 'buscaNome';
    }
    else {
        url = 'buscaImovel';
    }
    return await axios({ url: arquivoUrl + url + '/' + busca.keywords + '/' + busca.status, method: 'GET' })
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

const apagarImovel = async (_, payload) => {
    let id = payload.id;
    return await axios({ url: imovelUrl + 'deletarImovel' + '/' + id, data: payload, method: 'PUT' })
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

const loadQuantidadeImoveisInativos = async ({ commit }) => {
    return await axios({ url: imovelUrl + 'numeroDeAtivos', method: 'GET' })
        .then(response => {
            const payloadImoveisAtivos = response.data.numero;
            commit('quantidadeImoveisAtivos', payloadImoveisAtivos);
            return response.data
        }).catch(error => {
            console.log(error)
        })
};

const loadQuantidadeImoveisAtivos = async ({ commit }) => {
    return await axios({ url: imovelUrl + 'numeroDeInativos', method: 'GET' })
        .then(response => {
            const payloadImoveisInativos = response.data.numero;

            commit('quantidadeImoveisInativos', payloadImoveisInativos);
            return response.data
        }).catch(error => {
            console.log(error)
        })
};

const loadQuantidadeArquivosAtivos = async ({ commit }) => {
    return await axios({ url: arquivoUrl + 'numeroDeAtivos', method: 'GET' })
        .then(response => {
            const payloadArquivosAtivos = response.data.numero;
            commit('quantidadeArquivosAtivos', payloadArquivosAtivos);
            return response.data
        }).catch(error => {
            console.log(error)
        })
};

const loadQuantidadeArquivosInativos = async ({ commit }) => {
    return await axios({ url: arquivoUrl + 'numeroDeInativos', method: 'GET' })
        .then(response => {
            const payloadArquivosInativos = response.data.numero;

            commit('quantidadeArquivosInativos', payloadArquivosInativos);
            return response.data
        }).catch(error => {
            console.log(error)
        })
};

const loadQuantidadeArquivosSemImovel = async ({ commit }) => {
    return await axios({ url: arquivoUrl + 'numeroSemImovel', method: 'GET' })
        .then(response => {
            console.log(response.data)
            const payloadArquivosSemImovel = response.data.numero;

            commit('quantidadeArquivosSemImovel', payloadArquivosSemImovel);
            return response.data
        }).catch(error => {
            console.log(error)
        })
};

const loadArquivosInvalidos = async ({ commit }) => {
    return await axios({ url: arquivoUrl + 'buscarTodosInvalidos', method: 'GET' })
        .then(response => {
            const payloadArquivos = response.data;
            commit('arquivos', payloadArquivos);
            return response.data
        }).catch(error => {
            console.log(error)
        })
};

const loadImoveisInvalidos = async ({ commit }) => {
    return await axios({ url: imovelUrl + 'buscarTodosInvalidos', method: 'GET' })
        .then(response => {
            const payloadImoveis = response.data;
            commit('imoveis', payloadImoveis);
            return response.data
        }).catch(error => {
            console.log(error)
        })
};

const reativarArquivo = async (_, id) => {
    return await axios({ url: arquivoUrl + 'reativarArquivo' + '/' + id, method: 'PUT' })
        .then(response => {
            return response
        }).catch(error => {
            console.log(error)
        })
};


const reativarImovel = async (_, id) => {
    return await axios({ url: imovelUrl + 'reativarImovel' + '/' + id, method: 'PUT' })
        .then(response => {
            return response
        }).catch(error => {
            console.log(error)
        })
};


const apagarArquivoPermanentemente = async (_, id) => {
    return await axios({ url: arquivoUrl + 'deletarArquivoPermanente' + '/' + id, method: 'DELETE' })
        .then(response => {
            return response
        }).catch(error => {
            console.log(error)
        })
};

const apagarImovelPermanentemente = async (_, payload) => {
    let id = payload.id;
    return await axios({ url: imovelUrl + 'deletarImovelPermanente' + '/' + id, data: payload, method: 'DELETE' })
        .then(response => {
            return response
        }).catch(error => {
            console.log(error)
        })
};

const deletarTodosDocumentosAssociados = async (_, id) => {
    return await axios({ url: imovelUrl + 'deletarTodosDocumentosAssociados' + '/' + id, method: 'PUT' })
        .then(response => {
            return response
        }).catch(error => {
            console.log(error)
        })
};

const desassociarTodosDocumentos = async (_, id) => {
    return await axios({ url: imovelUrl + 'desassociarTodosDocumentos' + '/' + id, method: 'PUT' })
        .then(response => {
            return response
        }).catch(error => {
            console.log(error)
        })
};

const loadImoveisPorPagina = async ({ commit }, payload) => {
    let offset = payload.offset;
    let limit = payload.limit;
    let status = payload.status; // Ativo ou Inativo
    return await axios({ url: imovelUrl + 'imoveisPaginados' + status + '/' + offset + '/' + limit, method: 'GET' })
        .then(response => {
            const payloadImoveis = response.data.resultado;
            commit('imoveis', payloadImoveis);
            return response.data
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
    updateArquivo,
    loadQuantidadeImoveisInativos,
    loadQuantidadeImoveisAtivos,
    loadQuantidadeArquivosInativos,
    loadQuantidadeArquivosAtivos,
    loadQuantidadeArquivosSemImovel,
    loadArquivosInvalidos,
    loadImoveisInvalidos,
    reativarArquivo,
    reativarImovel,
    apagarArquivoPermanentemente,
    apagarImovelPermanentemente,
    loadArquivosSemImoveis,
    loadImoveisValidosEInvalidos,
    deletarTodosDocumentosAssociados,
    desassociarTodosDocumentos,
    loadImoveisPorPagina
};