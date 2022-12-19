<template>
    <LoadingSection v-if="isFetching.status"></LoadingSection>
    <div class="container margin-barra my-3">
        <h4 v-if="invalidesOrNot">
            <Transition name="bounce" mode="out-in">
                <font-awesome-icon icon="fa-solid fa-exclamation" class="static" @click="changeTagFilter('importante')"
                    v-if="tags.filterImportant == 0" />
                <font-awesome-icon icon="fa-solid fa-exclamation" class="static impIcon"
                    @click="changeTagFilter('importante')" v-else />
            </Transition>

            <Transition name="bounce" mode="out-in">
                <font-awesome-icon icon="fa fa-star" class="static" @click="changeTagFilter('favorito')"
                    v-if="tags.filterFav == 0" />
                <font-awesome-icon icon="fa fa-star" class="static favIcon" @click="changeTagFilter('favorito')"
                    v-else />
            </Transition>

            <Transition name="bounce" mode="out-in">
                <font-awesome-icon icon="fa-solid fa-triangle-exclamation" class="static"
                    @click="changeTagFilter('urgente')" v-if="tags.filterUrgent == 0" />
                <font-awesome-icon icon="fa-solid fa-triangle-exclamation" class="static urgIcon"
                    @click="changeTagFilter('urgente')" v-else />
            </Transition>
        </h4>
        <div class="row">
            <div class="col-md-5 col-4">
                <div class="form-check form-switch" v-if="this.$store.state.login.type == 'adm'">
                    <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault"
                        v-model="invalidesOrNot" @click="changeList()">
                    <label class="form-check-label" for="flexSwitchCheckDefault" v-if="invalidesOrNot">Ativos</label>
                    <label class="form-check-label" for="flexSwitchCheckDefault" v-else>Inativos</label>
                </div>
            </div>
            <div class="col-md-7 col-8">
                <div class="input-group margin-bottom-40">
                    <div class="input-group-btn barra">
                        <input class="form-control" name="busca" id="busca" placeholder="Digite sua busca"
                            v-model="keywords" />
                        <span>
                            <button type="submit" class="btn colors" @click="procuraImovel">
                                <font-awesome-icon icon="fa-solid fa-search" />
                            </button>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <h4 class="my-3 text-center">Lista de Imóveis</h4>
    <div class="container mb-6" v-if="displayListaImoveis.length == 0">
        <h4 v-if="invalidesOrNot">Sem imóveis cadastrados</h4>
        <h4 v-else>Sem imóveis inativos</h4>
    </div>
    <div class="row rowCard mb-6" v-else>
        <CardImovel class="col-sm-6 paddingZero" v-for="imovel in displayListaImoveis" :key="imovel.id" :id="imovel.id">
            <template v-slot:card-header>
                <div class="container">
                    <div class="row">
                        <div class="col-8">
                            <h3 class="card-title col" style="'display:flex'" @click="redirect(imovel)"
                                v-if="imovel.nome.length < 21">
                                {{
                                        imovel.nome
                                }}
                            </h3>
                            <h3 class="card-title col" style="'display:flex'" @click="redirect(imovel)" v-else>
                                {{
                                        imovel.nome.substring(0, 20) + "..."
                                }}
                            </h3>
                        </div>
                        <div class="col-3" v-if="invalidesOrNot">
                            <h3 class="d-flex justify-content-center">
                                <Transition name="bounce" mode="out-in">
                                    <font-awesome-icon icon="fa-solid fa-exclamation" class="static"
                                        v-if="imovel.importante == 0"
                                        @click="addTag(imovel.id, 'importante', imovel.importante, imovel.tags_id)" />
                                    <font-awesome-icon icon="fa-solid fa-exclamation" class="static impIcon" v-else
                                        @click="addTag(imovel.id, 'importante', imovel.importante, imovel.tags_id)" />
                                </Transition>

                                <Transition name="bounce" mode="out-in">
                                    <font-awesome-icon icon="fa fa-star" class="static" v-if="imovel.favorito == 0"
                                        @click="addTag(imovel.id, 'favorito', imovel.favorito, imovel.tags_id)" />
                                    <font-awesome-icon icon="fa fa-star" class="static favIcon" v-else
                                        @click="addTag(imovel.id, 'favorito', imovel.favorito, imovel.tags_id)" />
                                </Transition>

                                <Transition name="bounce" mode="out-in">
                                    <font-awesome-icon icon="fa-solid fa-triangle-exclamation" class="static"
                                        v-if="imovel.urgente == 0"
                                        @click="addTag(imovel.id, 'urgente', imovel.urgente, imovel.tags_id)" />
                                    <font-awesome-icon icon="fa-solid fa-triangle-exclamation" class="static urgIcon"
                                        v-else @click="addTag(imovel.id, 'urgente', imovel.urgente, imovel.tags_id)" />
                                </Transition>
                            </h3>
                        </div>
                        <div class="col-1">
                            <div class="dropdown" v-if="invalidesOrNot && this.$store.state.login.type == 'adm'">
                                <h3 class="d-flex justify-content-center">
                                    <font-awesome-icon icon="fa-solid fa-bars" class="dropdown-toggle" type="button"
                                        id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                    </font-awesome-icon>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                        <li><a class="dropdown-item"
                                                @click="openModal(imovel, 'desassocia')">Desassociar
                                                todos os Arquivos</a></li>
                                        <li><a class="dropdown-item"
                                                @click="openModal(imovel, 'deleteDocumentos')">Apagar
                                                todos os Arquivos</a></li>
                                    </ul>
                                </h3>

                            </div>
                        </div>
                    </div>
                </div>
            </template>
            <template v-slot:card-body>
                <strong>Cidade</strong>: {{ imovel.cidade }}-{{ imovel.estado }} <br><br>
                <strong>Preço</strong>: R$ {{ imovel.preco }} <br><br>
            </template>

            <template v-slot:card-footer v-if="invalidesOrNot">
                <button class="btn btn-sm btn-success" @click="redirect(imovel)">Detalhes</button>
                <button class="btn btn-sm btn-danger" @click="openModal(imovel, 'delete')"
                    v-if="this.$store.state.login.type == 'adm'">Apagar</button>
            </template>
            <template v-slot:card-footer v-else>
                <button class="btn btn-sm btn-success" @click="openModal(imovel, 'reativar')">Reativar</button>
                <button class="btn btn-sm btn-danger" @click="openModal(imovel, 'delete')"
                    v-if="this.$store.state.login.type == 'adm'">Apagar
                    Definitivamente</button>
            </template>
        </CardImovel>
        <Pagination :offset="offset" :total="total" :current="current + 1" :limit="limit" @change-page="changePage">
        </Pagination>
        <Modal @close="toggleModal" :modalActive="modalActive" :showCloseButton="true">
            <div v-if="modalDelete">
                <div v-if="invalidesOrNot">
                    <div class="modal-content" v-if="confirmation && !modalDesassocia">
                        <h1>Imóvel apagado com sucesso</h1>
                    </div>
                    <div class="modal-content" v-if="confirmation && modalDesassocia">
                        <h1 class="pre">{{ modalDesassocia }}</h1>
                    </div>
                    <div v-if="!confirmation && !modalDesassocia">
                        <div v-if="!tipoDeDelete" class="modal-content">
                            <h1 class="mb-5">Deseja realmente apagar o imóvel <b class="pre">{{ imovelNome }}</b>?</h1>
                            <button class="btn btn-sm btn-danger" @click="toggleDelete()">Apagar</button>
                        </div>
                        <div v-else class="modal-content">
                            <h1 class="mb-5">Deseja, ao apagar:</h1>
                            <button class="btn btn-sm btn-danger-reverse mb-3"
                                @click="apagarImovel(imovelId, 'Imovel', 'desassociaDocumentos', 'PUT')">Desassociar
                                Todos os Documentos Associados ao Imóvel</button>
                            <button class="btn btn-sm btn-danger"
                                @click="apagarImovel(imovelId, 'Imovel', 'deleteDocumentos', 'PUT')">Apagar Todos
                                os Documentos Associados ao Imóvel</button>
                        </div>
                    </div>
                    <div class="modal-content" v-if="!confirmation && modalDesassocia">
                        <h1 class="mb-5">{{ modalDesassocia }} <b class="pre">{{ imovelNome }}</b>?</h1>
                        <button class="btn btn-sm btn-danger" @click="desassociarDocumentos(imovelId)"
                            v-if="modalDesassocia == 'Deseja realmente desassociar todos os documentos do '">Desassociar
                            Todos
                            os Documentos do Imóvel</button>
                        <button class="btn btn-sm btn-danger" @click="apagarDocumentos(imovelId)"
                            v-if="modalDesassocia == 'Deseja realmente apagar todos os documentos associados ao '">Apagar
                            Todos
                            os Documentos do Imóvel</button>
                    </div>
                </div>
                <div v-else>
                    <div class="modal-content" v-if="confirmation">
                        <h1>Imóvel apagado definitivamente com sucesso</h1>
                    </div>
                    <div v-else>
                        <div v-if="!tipoDeDelete" class="modal-content">
                            <h1 class="mb-5">Deseja realmente apagar o imóvel <b class="pre">{{ imovelNome }}</b>
                                definitivamente?
                            </h1>
                            <button class="btn btn-sm btn-danger" @click="toggleDelete()">Apagar</button>
                        </div>
                        <div v-else class="modal-content">
                            <h1 class="mb-5">Deseja, ao apagar:</h1>
                            <button class="btn btn-sm btn-danger-reverse mb-3"
                                @click="apagarImovel(imovelId, 'ImovelPermanente', 'desassociaDocumentos', 'DELETE')">Desassociar
                                Todos os Documentos Associados ao Imóvel
                            </button>
                            <button class="btn btn-sm btn-danger mb-3"
                                @click="apagarImovel(imovelId, 'ImovelPermanente', 'deleteDocumentos', 'DELETE')">Apagar
                                Definitivamente Todos os Documentos Associados ao Imóvel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else>
                <div class="modal-content" v-if="confirmation">
                    <h1>Imóvel reativado com sucesso</h1>
                </div>
                <div class="modal-content" v-else>
                    <h1 class="mb-5">Deseja realmente reativar o imóvel <b class="pre">{{ imovelNome }}</b>?</h1>
                    <button class="btn btn-sm btn-success" @click="reativarImovel(imovelId)">Reativar</button>
                </div>
            </div>
        </Modal>
    </div>
</template>

<script>
import CardImovel from "../Utils/CardImovel.vue";
import { mapActions, mapGetters, mapState } from "vuex";
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faSearch, faStar, faExclamation, faTriangleExclamation, faBars } from '@fortawesome/free-solid-svg-icons'
import LoadingSection from "../Utils/LoadingSection.vue";
import Modal from "../Utils/ModalDefault.vue";
import { ref } from "vue";
import Pagination from "../Utils/PaginationOfLists.vue"
import axios from 'axios';
library.add(faSearch, faStar, faExclamation, faTriangleExclamation, faBars)
const imovelUrl = 'http://localhost:8000/imovel/';

export default {
    data() {
        return {
            keywords: '',
            confirmation: false,
            imovelId: '',
            imovelNome: '',
            invalidesOrNot: true,
            modalDelete: false,
            modalReativar: false,
            modalDesassocia: false,
            tags: {
                filterFav: 0,
                filterImportant: 0,
                filterUrgent: 0
            },
            offset: 0,
            limit: 10,
            total: 0,
            current: 0,
            busca: false
        }
    },

    components: { CardImovel, Modal, FontAwesomeIcon, LoadingSection, Pagination },

    setup() {
        const tipoDeDelete = ref(false);
        const toggleDelete = () => {
            tipoDeDelete.value = !tipoDeDelete.value;
        };

        const modalActive = ref(false);
        const toggleModal = () => {
            modalActive.value = !modalActive.value;
            if (tipoDeDelete.value) {
                tipoDeDelete.value = !tipoDeDelete.value;
            }
        };

        return { modalActive, toggleModal, tipoDeDelete, toggleDelete };
    },

    methods: {
        ...mapActions(["loadImoveis", "buscaImovel", "loadImoveisInvalidos",
            "loadImoveisPorPagina", "loadImoveisPorPaginaFiltrados", "buscaImovelFiltrado"]),

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
                status: status,
                offset: this.offset,
                limit: this.limit,
                token: this.$store.state.login.token
            }

            this.$store.dispatch('buscaImovel', payload).then(response => {
                this.busca = true;
                this.total = response.totalImoveis.totalImoveis;
            }).catch(error =>
                this.$store.commit('isFetching', { status: true, message: error })
            )
        },
        // procurar se busca estiver com filtro. o que fazer
        async procuraImovel() {
            this.$store.commit('isFetching', { status: true, message: 'Carregando...' });

            if (this.keywords.length == 0) {
                if (this.invalidesOrNot) {
                    await this.inicializaLista('Ativos', null);
                }
                else {
                    await this.inicializaLista('Inativos', null);
                }
            }
            else {
                this.tags.filterFav = 0;
                this.tags.filterImportant = 0;
                this.tags.filterUrgent = 0;
                await this.execSearchImovel();
                this.offset = 0;
                this.current = 0;
            }
            this.$store.commit('isFetching', { status: false, message: '' });
        },

        openModal(imovel, tipo) {
            this.modalDelete = false;
            this.modalReativar = false;
            this.confirmation = false;
            this.modalDesassocia = false;

            if (tipo == 'delete') {
                this.modalDelete = true;
            }
            else if (tipo == 'desassocia') {
                this.modalDelete = true;
                this.modalDesassocia = 'Deseja realmente desassociar todos os documentos do '
            }
            else if (tipo == 'deleteDocumentos') {
                this.modalDelete = true;
                this.modalDesassocia = 'Deseja realmente apagar todos os documentos associados ao '
            }
            else {
                this.modalReativar = true;

            }
            this.imovelId = imovel.id;
            this.imovelNome = imovel.nome;
            this.toggleModal();

        },
        // teoricamente tudo do crud tá ok no back.
        // ver o que falta no front, ir colocando as headers e ir testando
        // ja testei tudo ok... testar casos errados
        // ver de por token nos cookies.
        // ver negocio de tipagem... exibir algumas coisas só pra adm e tal. fazer um user nao adm pra testes
        // fazer tela de criar usuario. decidir se só adm vai ter ou n 

        async recalculaDepoisRemoverDaLista(status) {
            if ((this.total - 1) / this.limit == Math.floor(this.total / this.limit) && (this.current == Math.floor(this.total / this.limit))
                && (this.current != 0 && this.offset != 0)) {
                this.offset = this.offset - this.limit;
                this.current--;
            }

            if (this.busca) {
                if (this.tags.filterFav == 1 || this.tags.filterImportant == 1 || this.tags.filterUrgent == 1) {
                    let resultado;

                    resultado = await this.buscaImovelFiltrado({ offset: this.offset, limit: this.limit, tags: this.tags, keywords: this.keywords, status: 'A', token: this.$store.state.login.token });
                    this.total = resultado.totalImoveis.totalImoveis;
                }
                else {
                    await this.execSearchImovel();
                }
            }

            else {
                let resultado;

                if (this.tags.filterFav == 1 || this.tags.filterImportant == 1 || this.tags.filterUrgent == 1) {
                    resultado =
                        await this.loadImoveisPorPaginaFiltrados({ offset: this.offset, limit: this.limit, tags: this.tags, token: this.$store.state.login.token });
                }
                else {
                    resultado =
                        await this.loadImoveisPorPagina({ offset: this.offset, limit: this.limit, status: status, token: this.$store.state.login.token });
                }

                this.total = resultado.totalImoveis.totalImoveis;
            }

        },

        async apagarImovel(id, tipo, tipoDelete, method) {
            let payload = { id: id, tipoDelete: tipoDelete };
            this.$store.commit('isFetching', { status: true, message: 'Carregando...' });
            const headers = {
                "Authorization": "Bearer " + this.$store.state.login.token,
            };

            await axios({ url: imovelUrl + 'deletar' + tipo + '/' + id, data: payload, method: method, headers: headers })
                .then(async () => {
                    let status = 'Inativos';
                    if (tipo == 'Imovel') {
                        status = 'Ativos';
                    }
                    await this.recalculaDepoisRemoverDaLista(status);

                    this.$store.commit('isFetching', { status: false, message: '' });
                    this.confirmation = true;
                }).catch(error => {
                    this.$store.commit('isFetching', { status: true, message: error })
                })
        },

        async reativarImovel(id) {
            this.$store.commit('isFetching', { status: true, message: 'Carregando...' });
            const headers = {
                "Authorization": "Bearer " + this.$store.state.login.token,
            };

            await axios({ url: imovelUrl + 'reativarImovel' + '/' + id, method: 'PUT', headers: headers })
                .then(async () => {
                    await this.recalculaDepoisRemoverDaLista('Inativos');
                    this.confirmation = true;
                    this.$store.commit('isFetching', { status: false, message: '' });
                }).catch(error => {
                    this.$store.commit('isFetching', { status: true, message: error })
                })
        },

        async addTag(imovelId, type, value, tagId) {
            let values = { imovelId: imovelId, type: type, value: this.changeTagValue(value), hora: this.getNow(), tagId: tagId }
            let payload = { values: values, token: this.$store.state.login.token }

            await this.$store.dispatch('alterarTag', payload)
                .then(async () => {
                    await this.recalculaDepoisRemoverDaLista('Ativos');

                })
                .catch(error => this.$store.commit('isFetching', { status: true, message: error }))
        },

        async desassociarDocumentos(id) {
            const headers = {
                "Authorization": "Bearer " + this.$store.state.login.token,
            };
            this.$store.commit('isFetching', { status: true, message: 'Carregando...' });
            await axios({ url: imovelUrl + 'desassociarTodosDocumentos' + '/' + id, method: 'PUT', headers: headers })
                .then(() => {
                    this.modalDesassocia = 'Documentos do imóvel desassociados com sucesso';
                    this.confirmation = true;
                }).catch(error => {
                    this.$store.commit('isFetching', { status: true, message: error })
                })
            this.$store.commit('isFetching', { status: false, message: '' });

        },

        async apagarDocumentos(id) {
            const headers = {
                "Authorization": "Bearer " + this.$store.state.login.token,
            };

            this.$store.commit('isFetching', { status: true, message: 'Carregando...' });
            await axios({ url: imovelUrl + 'deletarTodosDocumentosAssociados' + '/' + id, method: 'PUT', headers: headers })
                .then(() => {
                    this.modalDesassocia = 'Documentos do imóvel apagados com sucesso';
                    this.confirmation = true;
                }).catch(error => {
                    this.$store.commit('isFetching', { status: true, message: error })
                })
            this.$store.commit('isFetching', { status: false, message: '' });

        },

        async changeTagFilter(tipo) {
            // ver se tiver em busca e se tiver com filtro. O que fazer.
            // começar daqui.
            this.$store.commit('isFetching', { status: true, message: 'Carregando...' });
            if (tipo == 'urgente') {
                this.tags.filterUrgent = this.changeTagValue(this.tags.filterUrgent);
            }
            else if (tipo == 'importante') {
                this.tags.filterImportant = this.changeTagValue(this.tags.filterImportant);
            }
            else {
                this.tags.filterFav = this.changeTagValue(this.tags.filterFav);
            }
            await this.inicializaLista(null, this.tags);
            this.$store.commit('isFetching', { status: false, message: '' });
        },

        changeTagValue(value) {
            if (value == 0) {
                return 1
            }
            else {
                return 0
            }
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

        async changeList() {
            this.modalActive = false;
            this.busca = false;
            this.keywords = '';
            this.tags.filterFav = 0;
            this.tags.filterImportant = 0;
            this.tags.filterUrgent = 0;
            this.current = 0;
            let status = 'Ativos';
            this.offset = 0;
            this.$store.commit('isFetching', { status: true, message: 'Carregando...' });
            if (this.invalidesOrNot) {
                status = 'Inativos';
            }
            await this.inicializaLista(status, null);
            this.$store.commit('isFetching', { status: false, message: '' });
        },

        async changePage(value) {
            let status = 'Ativos';
            if (!this.invalidesOrNot) {
                status = 'Inativos';
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
                if (this.tags.filterFav == 1 || this.tags.filterImportant == 1 || this.tags.filterUrgent == 1) {
                    await this.buscaImovelFiltrado({ offset: this.offset, limit: this.limit, tags: this.tags, keywords: this.keywords, status: 'A' });
                }
                else {
                    await this.execSearchImovel();
                }
            }
            else {
                if (this.tags.filterFav == 1 || this.tags.filterImportant == 1 || this.tags.filterUrgent == 1) {
                    await this.loadImoveisPorPaginaFiltrados({ offset: this.offset, limit: this.limit, tags: this.tags });
                }
                else {
                    await this.loadImoveisPorPagina({ offset: this.offset, limit: this.limit, status: status });
                }

            }
            this.$store.commit('isFetching', { status: false, message: '' });
        },

        async inicializaLista(status, tags) {
            this.offset = 0;
            this.current = 0;
            this.total = 0;

            let resultado;
            if (tags) {
                if (this.busca) {
                    resultado = await this.buscaImovelFiltrado({ offset: this.offset, limit: this.limit, tags: this.tags, keywords: this.keywords, status: 'A', token: this.$store.state.login.token })
                }
                else {
                    resultado = await this.loadImoveisPorPaginaFiltrados({ offset: this.offset, limit: this.limit, tags: this.tags, token: this.$store.state.login.token });
                }
            }
            else {
                this.tags.filterFav = 0;
                this.tags.filterImportant = 0;
                this.tags.filterUrgent = 0;
                resultado = await this.loadImoveisPorPagina({ offset: this.offset, limit: this.limit, status: status, token: this.$store.state.login.token });
            }
            this.total = resultado.totalImoveis.totalImoveis;
        }
    },

    computed: {
        ...mapGetters([
            "displayListaImoveis"
        ]),

        ...mapState([
            "isFetching",
        ]),

    },

    async created() {
        this.$store.commit('isFetching', { status: true, message: 'Carregando...' });
        await this.inicializaLista('Ativos', null);
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