import axios from 'axios';
const imovelUrl = 'http://localhost:8000/imovel/';
const arquivoUrl = 'http://localhost:8000/arquivo/';

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
            commit('arquivos', payloadArquivos);
            return response.data
        }).catch(error => {
            console.log(error)
        })
};

const loadImoveis = async ({ commit }, token) => {
    const headers = {
        "Authorization": "Bearer " + token,
    };
    return await axios({ url: imovelUrl + 'buscarTodosValidos', method: 'GET', headers: headers })
        .then(response => {
            const payloadImoveis = response.data;
            commit('imoveis', payloadImoveis);
            return response.data
        }).catch(error => {
            console.log(error)
        })
};

const loadImoveisValidosEInvalidos = async ({ commit }, token) => {
    const headers = {
        "Authorization": "Bearer " + token,
    };

    return await axios({ url: imovelUrl + 'buscarTodosValidosEInvalidos', method: 'GET', headers: headers })
        .then(response => {
            const payloadImoveis = response.data;
            commit('imoveis', payloadImoveis);
            return response.data
        }).catch(error => {
            console.log(error)
        })
};


const buscaImovel = async ({ commit }, busca) => {

    const headers = {
        "Authorization": "Bearer " + busca.token,
    };

    return await axios({ url: imovelUrl + 'busca' + '/' + busca.keywords + '/' + busca.status + '/' + busca.offset + '/' + busca.limit, method: 'GET', headers: headers })
        .then(response => {
            const payloadImoveis = response.data.resultado;
            commit('imoveis', payloadImoveis);
            return response.data
        }).catch(error => {
            console.log(error)
        })
};

const buscaImovelFiltrado = async ({ commit }, busca) => {
    let tags = busca.tags;

    const headers = {
        "Authorization": "Bearer " + busca.token
    };

    return await axios({ url: imovelUrl + 'buscarTodosValidosComFiltro' + '/' + busca.keywords + '/' + busca.status + '/' + busca.offset + '/' + busca.limit + '/' + tags.filterUrgent + '/' + tags.filterFav + '/' + tags.filterImportant, method: 'GET', headers: headers })
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


    const headers = {
        "Authorization": "Bearer " + busca.token
    };

    return await axios({ url: arquivoUrl + url + '/' + busca.keywords + '/' + busca.status + '/' + busca.offset + '/' + busca.limit, method: 'GET', headers: headers })
        .then(response => {
            const payloadArquivos = response.data.resultado;
            commit('arquivos', payloadArquivos);
            return response.data
        }).catch(error => {
            console.log(error)
        })
};

const alterarTag = async ({ commit }, payload) => {

    const headers = {
        "Authorization": "Bearer " + payload.token
    };

    return await axios({ url: imovelUrl + 'alterarTag' + '/' + payload.values.tagId, data: payload.values, method: 'PUT', headers: headers })
        .then(response => {
            commit('alterTag', payload);
            return response
        }).catch(error => {
            console.log(error)
        })
};

const loadQuantidadeImoveis = async ({ commit }, payload) => {
    const headers = {
        "Authorization": "Bearer " + payload.token
    };

    return await axios({ url: imovelUrl + 'numeroDe' + payload.nome, method: 'GET', headers: headers })
        .then(response => {
            const payloadImoveis = response.data.numero;
            commit('quantidadeImoveis' + payload.nome, payloadImoveis);
            return response.data
        }).catch(error => {
            console.log(error)
        })
};

const loadQuantidadeArquivos = async ({ commit }, payload) => {
    const headers = {
        "Authorization": "Bearer " + payload.token
    };

    return await axios({ url: arquivoUrl + 'numeroDe' + payload.nome, method: 'GET', headers: headers })
        .then(response => {
            const payloadArquivos = response.data.numero;
            commit('quantidadeArquivos' + payload.nome, payloadArquivos);
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

const loadImoveisPorPagina = async ({ commit }, payload) => {
    let offset = payload.offset;
    let limit = payload.limit;
    let status = payload.status;

    const headers = {
        "Authorization": "Bearer " + payload.token,
    };

    return await axios({ url: imovelUrl + 'imoveisPaginados' + status + '/' + offset + '/' + limit, method: 'GET', headers: headers })
        .then(response => {
            const payloadImoveis = response.data.resultado;
            commit('imoveis', payloadImoveis);
            return response.data
        }).catch(error => {
            console.log(error)
        })
};


const loadImoveisPorPaginaFiltrados = async ({ commit }, payload) => {
    let offset = payload.offset;
    let limit = payload.limit;
    let tags = payload.tags;

    const headers = {
        "Authorization": "Bearer " + payload.token,
    };


    return await axios({ url: imovelUrl + 'imoveisPaginadosComFiltro' + '/' + offset + '/' + limit + '/' + tags.filterUrgent + '/' + tags.filterFav + '/' + tags.filterImportant, method: 'GET', headers: headers })
        .then(response => {
            const payloadImoveis = response.data.resultado;
            commit('imoveis', payloadImoveis);
            return response.data
        }).catch(error => {
            console.log(error)
        })
};

const loadArquivosPorPagina = async ({ commit }, payload) => {
    let offset = payload.offset;
    let limit = payload.limit;
    let url;
    if (payload.status == 'semImovel') {
        url = 'ativosSemImovel';
    }
    else {
        url = 'arquivosPaginados' + payload.status;
    }
    const headers = {
        "Authorization": "Bearer " + payload.token,
    };

    return await axios({ url: arquivoUrl + url + '/' + offset + '/' + limit, method: 'GET', headers: headers })
        .then(response => {
            const payloadArquivos = response.data.resultado;
            commit('arquivos', payloadArquivos);
            return response.data
        }).catch(error => {
            console.log(error)
        })
};

export default {
    loadImoveis,
    buscaImovel,
    alterarTag,
    loadArquivos,
    buscaArquivo,
    loadQuantidadeImoveis,
    loadQuantidadeArquivos,
    loadArquivosInvalidos,
    loadImoveisInvalidos,
    loadArquivosSemImoveis,
    loadImoveisValidosEInvalidos,
    loadImoveisPorPagina,
    loadArquivosPorPagina,
    loadImoveisPorPaginaFiltrados,
    buscaImovelFiltrado
};