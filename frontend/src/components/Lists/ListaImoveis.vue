<template>
    <LoadingSection v-if="isFetching"></LoadingSection>
    <div class="container d-flex justify-content-center margin-barra my-3">
        <div class="row">
            <div class="col-3" v-if="invalidesOrNot">
                <Transition name="bounce" mode="out-in">
                    <font-awesome-icon icon="fa-solid fa-exclamation" class="static"
                        @click="changeTagFilter('importante')" v-if="tags.filterImportant == 0" />
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
            </div>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault"
                    v-model="invalidesOrNot" @click="changeList()">
                <label class="form-check-label" for="flexSwitchCheckDefault" v-if="invalidesOrNot">Ativos</label>
                <label class="form-check-label" for="flexSwitchCheckDefault" v-else>Inativos</label>
            </div>
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
    <div class="container mb-6" v-if="displayListaImoveis(tags).length == 0">
        <h4 v-if="invalidesOrNot">Sem imóveis cadastrados</h4>
        <h4 v-else>Sem imóveis inativos</h4>
    </div>
    <div class="row rowCard mb-6" v-else>
        <CardImovel class="col-sm-6 paddingZero" v-for="imovel in displayListaImoveis(tags)" :key="imovel.id"
            :id="imovel.id">
            <template v-slot:card-header>
                <div class="container">
                    <div class="row">
                        <div class="col-9">
                            <h3 class="card-title col" style="'display:flex'" @click="redirect(imovel)">{{
                            imovel.nome
                            }} {{ imovel.id
                                }}</h3>
                        </div>
                        <div class="col-3" v-if="invalidesOrNot">
                            <Transition name="bounce" mode="out-in">
                                <font-awesome-icon icon="fa-solid fa-exclamation" class="static"
                                    v-if=" imovel.importante == 0"
                                    @click="addTag(imovel.id, 'importante', imovel.importante)" />
                                <font-awesome-icon icon="fa-solid fa-exclamation" class="static impIcon" v-else
                                    @click="addTag(imovel.id, 'importante', imovel.importante)" />
                            </Transition>

                            <Transition name="bounce" mode="out-in">
                                <font-awesome-icon icon="fa fa-star" class="static" v-if="imovel.favorito == 0"
                                    @click="addTag(imovel.id, 'favorito', imovel.favorito)" />
                                <font-awesome-icon icon="fa fa-star" class="static favIcon" v-else
                                    @click="addTag(imovel.id, 'favorito', imovel.favorito)" />
                            </Transition>

                            <Transition name="bounce" mode="out-in">
                                <font-awesome-icon icon="fa-solid fa-triangle-exclamation" class="static"
                                    v-if="imovel.urgente == 0" @click="addTag(imovel.id, 'urgente', imovel.urgente)" />
                                <font-awesome-icon icon="fa-solid fa-triangle-exclamation" class="static urgIcon" v-else
                                    @click="addTag(imovel.id, 'urgente', imovel.urgente)" />
                            </Transition>

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
                <button class="btn btn-sm btn-danger" @click="openModal(imovel, 'delete')">Apagar</button>
                <button class="btn btn-sm btn-warning" @click="openModal(imovel, 'desassocia')">Desassociar todos os
                    Arquivos</button>
                <button class="btn btn-sm btn-primary" @click="openModal(imovel, 'deleteDocumentos')">Apagar todos os
                    Arquivos</button>

            </template>
            <template v-slot:card-footer v-else>
                <button class="btn btn-sm btn-success" @click="openModal(imovel, 'reativar')">Reativar</button>
                <button class="btn btn-sm btn-danger" @click="openModal(imovel, 'delete')">Apagar
                    Definitivamente</button>
            </template>
        </CardImovel>
        <Pagination :offset="offset" :total="total" :limit="limit" @change-page="changePage"></Pagination>
        <Modal @close="toggleModal" :modalActive="modalActive" :showCloseButton="true">
            <div v-if="modalDelete">
                <div v-if="invalidesOrNot">
                    <div class="modal-content" v-if="confirmation && !modalDesassocia">
                        <h1>Imóvel apagado com sucesso</h1>
                    </div>
                    <div class="modal-content" v-if="confirmation && modalDesassocia">
                        <h1>{{modalDesassocia}}</h1>
                    </div>
                    <div v-if="!confirmation && !modalDesassocia">
                        <div v-if="!tipoDeDelete" class="modal-content">
                            <h1 class="mb-5">Deseja realmente apagar o imóvel <b>{{ imovelNome }}</b>?</h1>
                            <button class="btn btn-sm btn-danger" @click="toggleDelete()">Apagar</button>
                        </div>
                        <div v-else class="modal-content">
                            <h1 class="mb-5">Deseja, ao apagar:</h1>
                            <button class="btn btn-sm btn-danger"
                                @click="apagarImovel(imovelId, 'Imovel', 'deleteDocumentos')">Apagar Todos
                                os Documentos Associados ao Imóvel</button>
                            <button class="btn btn-sm btn-danger"
                                @click="apagarImovel(imovelId, 'Imovel', 'desassociaDocumentos')">Desassociar
                                Todos os Documentos Associados ao Imóvel</button>
                        </div>
                    </div>
                    <div v-if="!confirmation && modalDesassocia">
                        <h1 class="mb-5">{{modalDesassocia}} <b>{{ imovelNome }}</b>?</h1>
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
                            <h1 class="mb-5">Deseja realmente apagar o imóvel <b>{{ imovelNome }}</b>
                                definitivamente?
                            </h1>
                            <button class="btn btn-sm btn-danger" @click="toggleDelete()">Apagar</button>
                        </div>
                        <div v-else class="modal-content">
                            <h1 class="mb-5">Deseja, ao apagar:</h1>
                            <button class="btn btn-sm btn-danger"
                                @click="apagarImovel(imovelId, 'ImovelPermanentemente', 'deleteDocumentos')">Apagar
                                Definitivamente
                                Todos
                                os Documentos Associados ao Imóvel</button>
                            <button class="btn btn-sm btn-danger"
                                @click="apagarImovel(imovelId, 'ImovelPermanentemente', 'desassociaDocumentos')">Desassociar
                                Todos os Documentos Associados ao Imóvel</button>
                        </div>

                    </div>
                </div>
            </div>
            <div v-else>
                <div class="modal-content" v-if="confirmation">
                    <h1>Imóvel reativado com sucesso</h1>
                </div>
                <div class="modal-content" v-else>
                    <h1 class="mb-5">Deseja realmente reativar o imóvel <b>{{ imovelNome }}</b>?</h1>
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
import { faSearch, faStar, faExclamation, faTriangleExclamation } from '@fortawesome/free-solid-svg-icons'
import LoadingSection from "../Utils/LoadingSection.vue";

library.add(faSearch, faStar, faExclamation, faTriangleExclamation)
// adicionar isso no X do modal e tirar o link do fontawesome do index
// ver de mudar cor do icons e tornálos clicáveis pra fazer os botões de tag. 
// design: botar os "excluir docs" e "desassociar docs" como opções nos cards
import Modal from "../Utils/ModalDefault.vue";
import { ref } from "vue";
import Pagination from "../Utils/PaginationOfLists.vue"

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
        ...mapActions(["loadImoveis", "buscaImovel", "loadImoveisInvalidos", "loadImoveisAtivosPorPagina"]),

        async procuraImovel() {
            this.$store.commit('isFetching', true);

            if (this.keywords.length == 0) {
                if (this.invalidesOrNot) {
                    await this.loadImoveis();
                }
                else {
                    await this.loadImoveisInvalidos();
                }
            }
            let status;
            if (this.invalidesOrNot) {
                status = 'A';
            }
            else {
                status = 'I'
            }

            let payload = {
                keywords: this.keywords,
                status: status
            }

            this.$store.dispatch('buscaImovel', payload).catch(error => console.log(error))
            this.$store.commit('isFetching', false);

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

        /// pensar pra ver se tem alguma forma do modal de exclusão funcionar sem os negocios do data(). Mas por enquanto a solução atual funciona

        async apagarImovel(id, tipo, tipoDelete) {
            let payload = { id: id, tipoDelete: tipoDelete };
            await this.$store.dispatch('apagar' + tipo, payload)
                .then(() => {
                    if (tipo == 'Imovel') {
                        this.loadImoveis();
                    }
                    else {
                        this.loadImoveisInvalidos();
                    }
                    this.confirmation = true;
                }).catch(error => console.log(error))
        },

        async reativarImovel(id) {
            await this.$store.dispatch('reativarImovel', id)
                .then(() => {
                    this.loadImoveisInvalidos();
                    this.confirmation = true;
                }).catch(error => console.log(error))
        },

        async addTag(id, type, value) {
            let payload = { id: id, type: type, value: this.changeTagValue(value), hora: this.getNow() }

            await this.$store.dispatch('alterarTag', payload).catch(error => console.log(error))
        },

        async desassociarDocumentos(id) {
            this.$store.commit('isFetching', true);

            await this.$store.dispatch('desassociarTodosDocumentos', id)
                .then(() => {
                    this.modalDesassocia = 'Documentos do imóvel desassociados com sucesso';
                    this.confirmation = true;
                }).catch(error => console.log(error))
            this.$store.commit('isFetching', false);

        },

        async apagarDocumentos(id) {
            this.$store.commit('isFetching', true);

            await this.$store.dispatch('deletarTodosDocumentosAssociados', id)
                .then(() => {
                    this.modalDesassocia = 'Documentos do imóvel apagados com sucesso';
                    this.confirmation = true;
                }).catch(error => console.log(error))
            this.$store.commit('isFetching', false);

        },

        async changeTagFilter(tipo) {
            if (tipo == 'urgente') {
                this.tags.filterUrgent = this.changeTagValue(this.tags.filterUrgent);
            }
            else if (tipo == 'importante') {
                this.tags.filterImportant = this.changeTagValue(this.tags.filterImportant);
            }
            else {
                this.tags.filterFav = this.changeTagValue(this.tags.filterFav);
            }
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

            // passando só id via props e o resto por vuex... mas daria pra passar tudo por props passando parametro por parametro. mas seria paia.
        },
        async changeList() {
            this.keywords = '';
            this.tags.filterFav = 0;
            this.tags.filterImportant = 0;
            this.tags.filterUrgent = 0;
            this.current = 0;
            let status = 'Ativos';
            this.offset = 0;
            this.$store.commit('isFetching', true);
            if (this.invalidesOrNot) {
                status = 'Inativos';
            }
            const teste =
                await this.loadImoveisAtivosPorPagina({ offset: this.offset, limit: this.limit, status: status });
            this.total = teste.totalImoveis.totalImoveis;
            this.$store.commit('isFetching', false);
        },

        changePage(value) {
            let status = 'Ativos';
            if (!this.invalidesOrNot) {
                status = 'Inativos';
            }
            if (this.current == value) {
                return;
            }
            else if (this.current > value) {
                this.offset = this.offset - this.limit;
            }
            else if (this.current < value) {
                this.offset = this.offset + this.limit;
            }
            this.current = value;
            this.$store.commit('isFetching', true);
            this.loadImoveisAtivosPorPagina({ offset: this.offset, limit: this.limit, status: status });
            this.$store.commit('isFetching', false);
        },
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
        this.$store.commit('isFetching', true);
        await this.loadImoveis();
        const teste = await this.loadImoveisAtivosPorPagina({ offset: this.offset, limit: this.limit, status: 'Ativos' });
        // https://dedicio.medium.com/como-criar-um-componente-de-pagina%C3%A7%C3%A3o-em-vue-js-86fed480e45b
        // API tem que estar parecida com a desse exemplo... ta melhorando.
        // ja ta retornando total, fazendo coisa com offset e limit. 
        // ai organizar os load tudo e organizar o offset e limit local, o que tem que ser atribuido a que e o que tem que ir pro component
        console.log(teste)
        this.total = teste.totalImoveis.totalImoveis;
        this.$store.commit('isFetching', false);

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