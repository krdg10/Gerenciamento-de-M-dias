<template>
    <LoadingSection v-if="isFetching"></LoadingSection>
    <div class="container margin-barra my-3">
        <div class="row">
            <div class="col-md-3 col-5">
                <div class="form-check form-switch" v-if="invalidesOrNot">
                    <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault2"
                        v-model="semImovel" @change="changeList()">
                    <label class="form-check-label" for="flexSwitchCheckDefault2">Sem imóvel</label>
                </div>
                <div class="form-check form-switch"
                    v-if="!semImovel && this.$store.state.login.type == 'adm' || this.$store.state.login.type == 'master'">
                    <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault"
                        v-model="invalidesOrNot" @change="changeList()">
                    <label class="form-check-label" for="flexSwitchCheckDefault" v-if="invalidesOrNot">Ativos</label>
                    <label class="form-check-label" for="flexSwitchCheckDefault" v-else>Inativos</label>
                </div>
            </div>
            <div class="col-md-7 col-7">
                <div class="row">
                    <div class="col-md-8 col-8">
                        <div class="row">
                            <div class="col-md-4 paddingZero" v-if="!semImovel">
                                <select class="form-control" name="tipoBusca" id="tipoBusca" v-model="tipoBusca"
                                    @change="keywords = ''">
                                    <option value="nome" selected>Nomes</option>
                                    <option value="imovel">Imovel</option>
                                </select>
                            </div>
                            <div class="col-md-8 paddingZero" v-if="!semImovel">
                                <input class="form-control" name="busca" id="busca" placeholder="Digite sua busca"
                                    v-model="keywords" v-if="tipoBusca == 'nome'" />
                                <select class="form-control" id="busca" placeholder="Imóvel associado ao arquivo"
                                    name="busca" v-model="keywords" v-else>
                                    <option value="" selected>Selecione o imóvel</option>
                                    <option v-for="imovel in displayListaImoveis" :value="imovel.id" :key="imovel.id">
                                        {{ imovel.id }} - {{ imovel.nome }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1 col-4 paddingZero" v-if="!semImovel">
                        <button type="submit" class="btn colors button-max" @click="procuraArquivo()">
                            <font-awesome-icon icon="fa-solid fa-search" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container mb-6" v-if="displayListaArquivos.length == 0">
        <h4 v-if="invalidesOrNot">Sem arquivos cadastrados</h4>
        <h4 v-else>Sem nenhum arquivo inativo</h4>
    </div>
    <div class="row rowCard mb-6" v-else>
        <CardImovel class="col-sm-6 paddingZero" v-for="arquivo in displayListaArquivos" :key="arquivo.id"
            :id="arquivo.id">
            <template v-slot:card-header>
                <h3 class="card-title">{{ arquivo.nome }}</h3>
            </template>
            <template v-slot:card-body>
                <div class="container-fluid justify-content-center d-flex mb-4">
                    <a :href="baseUrl + arquivo.caminho" class="color-black" download>
                        <font-awesome-icon icon="fa-solid fa-image" size="6x"
                            v-if="imageTypes.includes(arquivo.caminho.split('.')[1])" />
                        <font-awesome-icon icon="fa-solid fa-file-word" size="6x"
                            v-if="arquivo.caminho.split('.')[1] == 'doc' || arquivo.caminho.split('.')[1] == 'docx'" />
                        <font-awesome-icon icon="fa-solid fa-file-excel" size="6x"
                            v-if="arquivo.caminho.split('.')[1] == 'xls' || arquivo.caminho.split('.')[1] == 'xlsx'" />
                        <font-awesome-icon icon="fa-solid fa-file-pdf" size="6x"
                            v-if="arquivo.caminho.split('.')[1] == 'pdf'" />
                    </a>
                </div>
                <strong>Nome do Arquivo</strong>: {{ arquivo.nome }} <br>
                <strong>Nome Original</strong>: {{ arquivo.nome_original.split(".")[0] }} <br>
                <a @click="redirect(displayImovelById(arquivo.imovel_id))" class="color-black"
                    v-if="arquivo.imovel_id && displayImovelById(arquivo.imovel_id).ativo == 'A'"><strong>Imovel
                        Associado</strong>: {{
                                displayImovelById(arquivo.imovel_id).nome
                        }} <br></a>
                <div v-else>
                    <a class="color-black color-black-without-hover" v-if="!arquivo.imovel_id">
                        <strong>Imovel Associado</strong>: Nenhum
                    </a>
                    <a class="color-black color-black-without-hover" v-else>
                        <strong>Imovel Associado</strong>: {{
                                displayImovelById(arquivo.imovel_id).nome
                        }} <strong>(Deletado)</strong><br>
                    </a>
                </div>
                <strong>Tipo do Arquivo</strong>: {{ arquivo.caminho.split(".")[1] }} <br><br>

            </template>
            <template v-slot:card-footer v-if="invalidesOrNot">
                <button class="btn btn-sm btn-success" @click="openModal(arquivo, 'edit')"
                    v-if="this.$store.state.login.type == 'adm' || this.$store.state.login.type == 'master'">
                    Editar</button>
                <button class="btn btn-sm btn-danger" @click="openModal(arquivo, 'delete')"
                    v-if="this.$store.state.login.type == 'adm' || this.$store.state.login.type == 'master'">Apagar</button>
            </template>
            <template v-slot:card-footer v-else>
                <button class="btn btn-sm btn-success" @click="openModal(arquivo, 'edit')"
                    v-if="!(arquivo.imovel_id && displayImovelById(arquivo.imovel_id).ativo == 'I') && this.$store.state.login.type == 'adm' || this.$store.state.login.type == 'master'">Reativar</button>
                <button class="btn btn-sm btn-primary" @click="openModal(arquivo, 'reactiveImovel')" v-else>Reativar
                    Imóvel do
                    Documento</button> <!-- v-else ver essa fita -->
                <button class="btn btn-sm btn-danger" @click="openModal(arquivo, 'delete')"
                    v-if="this.$store.state.login.type == 'adm' || this.$store.state.login.type == 'master'">Apagar
                    Definitivamente</button>
            </template>
        </CardImovel>
        <Pagination :offset="offset" :total="total" :limit="limit" :current="current + 1" @change-page="changePage">
        </Pagination>
        <Modal @close="toggleModal" :modalActive="modalActive">
            <div v-if="modalDelete">
                <div v-if="invalidesOrNot">
                    <div class="modal-content" v-if="confirmation">
                        <h1>Arquivo apagado com sucesso</h1>
                    </div>
                    <div class="modal-content" v-else>
                        <h1 class="mb-5">Deseja realmente apagar o arquivo {{ arquivoNome }}?</h1>
                        <button class="btn btn-sm btn-danger"
                            @click="apagarArquivo(arquivoId, 'Arquivo', 'PUT')">Apagar</button>
                    </div>
                </div>
                <div v-else>
                    <div class="modal-content" v-if="confirmation">
                        <h1>Arquivo apagado permanentemente com sucesso</h1>
                    </div>
                    <div class="modal-content" v-else>
                        <h1 class="mb-5">Deseja realmente apagar o arquivo {{ arquivoNome }} permanentemente?</h1>
                        <button class="btn btn-sm btn-danger mt-3"
                            @click="apagarArquivo(arquivoId, 'ArquivoPermanente', 'DELETE')">Apagar</button>
                    </div>
                </div>
            </div>
            <div v-else>
                <div v-if="invalidesOrNot">
                    <div v-if="edit" class="modal-content">
                        <h3 class="text-center">Editar arquivo <b>{{ arquivoNome }}</b></h3>
                        <UploadArquivo :imovelProps="imovelProps" ref="formulario">
                            <template v-slot:button-submit>
                                <div class="container mt-4">
                                    <div class="row">
                                        <div class="text-center">
                                            <button class="btn btn-primary" @click="onCompleteEdit()">Submit</button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </UploadArquivo>
                    </div>
                    <div class="modal-content" v-else>
                        <h1>Arquivo Editado Com Sucesso</h1>
                    </div>
                </div>
                <div v-else>
                    <div v-if="confirmation && !reactiveImovel" class="modal-content">
                        <h1>Arquivo reativado com sucesso</h1>
                    </div>
                    <div v-if="!confirmation && !reactiveImovel" class="modal-content">
                        <h1 class="mb-5">Deseja realmente reativar o arquivo {{ arquivoNome }}?</h1>
                        <button class="btn btn-sm btn-success" @click="reativarArquivo(arquivoId)">Reativar</button>
                    </div>
                    <div v-if="confirmation && reactiveImovel" class="modal-content">
                        <h1>Imóvel do arquivo reativado com sucesso</h1>
                    </div>
                    <div v-if="!confirmation && reactiveImovel" class="modal-content">
                        <h1 class="mb-5">Deseja realmente reativar o imóvel do arquivo {{ arquivoNome }}?</h1>
                        <button class="btn btn-sm btn-primary"
                            @click="reativarImovel(arquivoImovelId)">Reativar</button>
                    </div>
                </div>
            </div>
        </Modal>
    </div>
</template>

<script>
import CardImovel from "../Utils/CardImovel.vue";
import { mapActions, mapGetters, mapState } from "vuex";
import Modal from "../Utils/ModalDefault.vue";
import { ref } from "vue";
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faSearch, faFilePdf, faImage, faFileWord, faFileExcel } from '@fortawesome/free-solid-svg-icons'
import UploadArquivo from '../Forms/UploadArquivos.vue'
import LoadingSection from "../Utils/LoadingSection.vue";
import Pagination from "../Utils/PaginationOfLists.vue"
import axios from 'axios';

library.add(faSearch, faFilePdf, faImage, faFileWord, faFileExcel)
const arquivoUrl = 'http://localhost:8000/arquivo/';
const imovelUrl = 'http://localhost:8000/imovel/';

export default {
    data() {
        return {
            keywords: '',
            confirmation: false,
            arquivoId: '',
            arquivoNome: '',
            modalDelete: false,
            arquivoImovel: '',
            edit: true,
            imovelProps: true,
            tipoBusca: 'nome',
            baseUrl: 'http://localhost:8000/',
            imageTypes: ['png', 'jpg', 'jpeg'],
            invalidesOrNot: true,
            semImovel: false,
            reactiveImovel: false,
            arquivoImovelId: '',
            offset: 0,
            limit: 10,
            total: 0,
            current: 0,
            busca: false
        }
    },

    props: {
        propsSemImovel: {
            type: [String, Boolean],
            default: false,
        },
    },

    components: { CardImovel, Modal, FontAwesomeIcon, UploadArquivo, LoadingSection, Pagination },

    setup() {
        const modalActive = ref(false);
        const toggleModal = () => {
            modalActive.value = !modalActive.value;
        };
        return { modalActive, toggleModal };
    },

    methods: {
        ...mapActions(["loadArquivos", "buscaArquivo", "loadImoveisValidosEInvalidos", "reativarImovel", "loadArquivosPorPagina"]),

        async execSearchImovel() {
            let status;
            if (this.invalidesOrNot) {
                status = 'A';
            }
            else {
                status = 'I'
            }

            let payload = {
                keywords: this.keywords,
                tipo: this.tipoBusca,
                status: status,
                offset: this.offset,
                limit: this.limit,
                token: this.$store.state.login.token
            }

            this.$store.dispatch('buscaArquivo', payload)
                .then(response => {
                    this.busca = true;
                    this.total = response.totalImoveis.totalImoveis;
                }).catch(error => console.log(error))
        },

        async procuraArquivo() {
            this.$store.commit('isFetching', { status: true, message: 'Carregando...' });
            if (this.keywords.length == 0) {
                if (this.invalidesOrNot) {
                    await this.loadArquivosPorPagina({ offset: this.offset, limit: this.limit, status: 'Ativos', token: this.$store.state.login.token });
                }
                else {
                    await this.loadArquivosPorPagina({ offset: this.offset, limit: this.limit, status: 'Inativos', token: this.$store.state.login.token });
                }
            }
            else {
                await this.execSearchImovel();
                this.offset = 0;
                this.current = 0;
            }
            this.$store.commit('isFetching', { status: false, message: '' });
        },

        async openModal(arquivo, tipo) {
            this.modalDelete = false;
            this.confirmation = false;
            this.edit = false;
            this.reactiveImovel = false;
            this.arquivoId = arquivo.id;
            this.arquivoNome = arquivo.nome;
            this.arquivoImovelId = '';

            if (tipo == 'delete') {
                this.modalDelete = true;
            }
            else if (tipo == 'reactiveImovel') {
                this.reactiveImovel = true;
                this.arquivoImovelId = arquivo.imovel_id;
            }
            else {
                this.edit = true;
            }

            if (this.invalidesOrNot) {
                if (this.edit) {
                    await this.$refs.formulario;
                    if (arquivo.imovel_id) {
                        this.$refs.formulario.imovel = await this.displayListaImoveis.find(x => x.id == arquivo.imovel_id).id;
                    }
                    this.$refs.formulario.nome = arquivo.nome;
                }
            }
            this.toggleModal();
        },

        async apagarArquivo(id, tipo, method) {
            this.$store.commit('isFetching', { status: true, message: 'Carregando...' });
            const headers = {
                "Authorization": "Bearer " + this.$store.state.login.token,
            };

            await axios({ url: arquivoUrl + 'deletar' + tipo + '/' + id, method: method, headers: headers })
                .then(async () => {
                    if (tipo == 'Arquivo') {
                        if (this.semImovel) {
                            await this.recalculaDepoisRemoverDaLista('semImovel');
                        }
                        else {
                            await this.recalculaDepoisRemoverDaLista('Ativos');
                        }
                    }
                    else {
                        await this.recalculaDepoisRemoverDaLista('Inativos');
                    }
                    this.confirmation = true;
                }).catch(error => {
                    if (error.response.status == 401) {
                        this.$store.commit('isLoggedOff');
                        this.$router.push({ name: 'home' });
                        this.toggleModal();
                        return;
                    }

                    console.log(error)
                })
            this.$store.commit('isFetching', { status: false, message: '' });
        },

        async reativarArquivo(id) {
            this.$store.commit('isFetching', { status: true, message: 'Carregando...' });
            const headers = {
                "Authorization": "Bearer " + this.$store.state.login.token,
            };

            await axios({ url: arquivoUrl + 'reativarArquivo' + '/' + id, method: 'PUT', headers: headers })
                .then(async () => {
                    await this.recalculaDepoisRemoverDaLista('Inativos');
                    this.confirmation = true;
                }).catch(error => {
                    if (error.response.status == 401) {
                        this.$store.commit('isLoggedOff');
                        this.$router.push({ name: 'home' });
                        this.toggleModal();
                        return;
                    }
                    console.log(error)
                })
            this.$store.commit('isFetching', { status: false, message: '' });
        },

        async reativarImovel(id) {
            this.$store.commit('isFetching', { status: true, message: 'Carregando...' });
            const headers = {
                "Authorization": "Bearer " + this.$store.state.login.token,
            };

            await axios({ url: imovelUrl + 'reativarImovel' + '/' + id, method: 'PUT', headers: headers })
                .then(async () => {
                    //this.loadArquivosInvalidos();
                    // await this.recalculaDepoisRemoverDaLista('Inativos');
                    await this.loadImoveisValidosEInvalidos();
                    this.confirmation = true;
                }).catch(error => {
                    if (error.response.status == 401) {
                        this.$store.commit('isLoggedOff');
                        this.$router.push({ name: 'home' });
                        this.toggleModal();
                        return;
                    }
                    console.log(error)
                }
                );
            this.$store.commit('isFetching', { status: false, message: '' });
        },

        getNow() {
            const today = new Date();
            const date = today.getFullYear() + '-' + (today.getMonth() + 1) + '-' + today.getDate();
            const time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
            const dateTime = date + ' ' + time;
            return dateTime;
        },

        redirect(imovel) {
            let id = imovel.id;
            this.$store.commit('imovel', imovel);
            this.$router.push({ name: 'novoImovel', params: { id: id } })
        },

        async onCompleteEdit() {
            let arquivo = {
                id: this.arquivoId,
                nome: this.$refs.formulario.nome,
                imovel: this.$refs.formulario.imovel,
                data_edicao: this.getNow(),
            }

            const headers = {
                "Authorization": "Bearer " + this.$store.state.login.token,
            };
            this.$store.commit('isFetching', { status: true, message: 'Carregando...' });
            await axios({ url: arquivoUrl + 'editar' + '/' + arquivo.id, data: arquivo, method: 'PUT', headers: headers })
                .then(async () => {
                    if (this.semImovel) {
                        if ((this.arquivoImovel == null || this.arquivoImovel == '') && (arquivo.imovel != null && arquivo.imovel != '')) {
                            await this.recalculaDepoisRemoverDaLista('semImovel');
                        }
                        else {
                            await this.loadArquivosPorPagina({ offset: this.offset, limit: this.limit, status: 'semImovel', token: this.$store.state.login.token });
                        }
                    }
                    else {
                        await this.loadArquivosPorPagina({ offset: this.offset, limit: this.limit, status: 'Ativos', token: this.$store.state.login.token });
                    }
                    this.edit = false;
                    this.$store.commit('isFetching', { status: false, message: '' });
                }).catch(error => {
                    this.toggleModal();
                    let message;
                    if (error.response.status == 401) {
                        this.$store.commit('isLoggedOff');
                        this.$router.push({ name: 'home' });
                        this.toggleModal();
                        return;
                    }

                    if (typeof error.response.data == 'string') {
                        message = error.response.data;
                    }
                    else if (typeof error.response.data[0] == 'string' && error.response.data[0].length > 1) {
                        message = error.response.data[0];
                    }
                    else {
                        message = Object.values(error.response.data[0])[0][0];
                    }
                    this.$store.commit('isFetching', { status: true, message: message });
                })

        },

        async changeList() {
            this.keywords = '';
            this.modalActive = false;
            this.$store.commit('isFetching', { status: true, message: 'Carregando...' });
            if (this.semImovel) {
                await this.inicializaLista('semImovel');
            }
            else {
                if (this.invalidesOrNot) {
                    await this.inicializaLista('Ativos');
                }
                else {
                    await this.inicializaLista('Inativos');
                }
            }
            this.$store.commit('isFetching', { status: false, message: '' });
        },

        async changePage(value) {
            let status = 'Ativos';
            if (!this.invalidesOrNot) {
                status = 'Inativos';
            }
            if (this.semImovel) {
                status = 'semImovel'
            }
            if (this.current == value) {
                return;
            }
            else if (this.current > value) {
                this.offset = this.offset - (this.limit * (this.current - value));
            }
            else if (this.current < value) {
                this.offset = this.offset + (this.limit * (value - this.current));
            }
            this.current = value;
            this.$store.commit('isFetching', { status: true, message: 'Carregando...' });
            if (this.busca) {
                await this.execSearchImovel();
            }
            else {
                await this.loadArquivosPorPagina({ offset: this.offset, limit: this.limit, status: status, token: this.$store.state.login.token });
            }
            this.$store.commit('isFetching', { status: false, message: '' });
        },

        async inicializaLista(status) {
            this.busca = false;
            this.offset = 0;
            this.current = 0;
            this.total = 0;
            const resultado = await this.loadArquivosPorPagina({ offset: this.offset, limit: this.limit, status: status, token: this.$store.state.login.token });
            this.total = resultado.totalImoveis.totalImoveis;
        },

        async recalculaDepoisRemoverDaLista(status) {
            if ((this.total - 1) / this.limit == Math.floor(this.total / this.limit) && (this.current == Math.floor(this.total / this.limit))
                && (this.current != 0 && this.offset != 0)) {
                this.offset = this.offset - this.limit;
                this.current--;
            }

            if (this.busca) {
                await this.execSearchImovel();
            }
            else {
                let resultado;
                resultado =
                    await this.loadArquivosPorPagina({ offset: this.offset, limit: this.limit, status: status, token: this.$store.state.login.token });
                this.total = resultado.totalImoveis.totalImoveis;
            }
        },
    },

    computed: {
        ...mapGetters([
            "displayListaArquivos",
            "displayListaImoveis",
            "displayImovelById"
        ]),

        ...mapState([
            "isFetching",
        ]),

        schema() {
            const simpleSchema = {
                nome: 'required|max:50'
            }
            return simpleSchema;
        },

    },

    async created() {
        this.$store.commit('isFetching', { status: true, message: 'Carregando...' });
        await this.loadImoveisValidosEInvalidos(this.$store.state.login.token);
        if (this.propsSemImovel) {
            this.semImovel = true;
            await this.inicializaLista('semImovel');
        }
        else {
            await this.inicializaLista('Ativos');
        }
        this.$store.commit('isFetching', { status: false, message: '' });
    },
}


</script>

<style lang="scss" scoped>
.home {
    background-color: rgba(0, 176, 234, 0.5);
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;

    .modal-content {
        display: flex;
        flex-direction: column;

        h1,
        p {
            margin-bottom: 16px;
        }

        h1 {
            font-size: 32px;
        }

        p {
            font-size: 18px;
        }
    }
}
</style>