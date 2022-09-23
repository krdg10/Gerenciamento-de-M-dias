<template>
    <LoadingSection v-if="isFetching"></LoadingSection>
    <div class="container d-flex justify-content-center margin-barra my-3">
        <div class="row">
            <div class="input-group paddingZeroLeft margin-bottom-40">
                <div class="input-group-btn barra container">
                    <div class="row">
                        <div class="form-check form-switch" v-if="invalidesOrNot">
                            <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault2"
                                v-model="semImovel" @change="changeList()">
                            <label class="form-check-label" for="flexSwitchCheckDefault2">Sem imóvel</label>
                        </div>
                        <div class="form-check form-switch" v-if="!semImovel">
                            <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault"
                                v-model="invalidesOrNot" @change="changeList()">
                            <label class="form-check-label" for="flexSwitchCheckDefault"
                                v-if="invalidesOrNot">Ativos</label>
                            <label class="form-check-label" for="flexSwitchCheckDefault" v-else>Inativos</label>
                        </div>
                        <div class="col-4 paddingZero" v-if="!semImovel">
                            <select class="form-control" name="tipoBusca" id="tipoBusca" v-model="tipoBusca"
                                @change="keywords = ''">
                                <option value="nome" selected>Nomes</option>
                                <option value="imovel">Imovel</option>
                            </select>
                        </div>
                        <div class="col-7 paddingZero" v-if="!semImovel">
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
                        <div class="col-1 paddingZero" v-if="!semImovel">
                            <span>
                                <button type="submit" class="btn colors" @click="procuraArquivo()">
                                    <font-awesome-icon icon="fa-solid fa-search" />
                                </button>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container" v-if="displayListaArquivos.length == 0">
        <h4 v-if="invalidesOrNot">Sem arquivos cadastrados</h4>
        <h4 v-else>Sem nenhum arquivo inativo</h4>
    </div>
    <div class="row rowCard" v-else>
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
                <button class="btn btn-sm btn-success" @click="openModal(arquivo, 'edit')">Editar</button>
                <button class="btn btn-sm btn-danger" @click="openModal(arquivo, 'delete')">Apagar</button>
            </template>
            <template v-slot:card-footer v-else>
                <button class="btn btn-sm btn-success" @click="openModal(arquivo, 'edit')"
                    v-if="!(arquivo.imovel_id && displayImovelById(arquivo.imovel_id).ativo == 'I')">Reativar</button>
                <button class="btn btn-sm btn-primary" @click="openModal(arquivo, 'reactiveImovel')" v-else>Reativar
                    Imóvel do
                    Documento</button>
                <!-- botar um v-if aqui pra nao aparecer quando imovel = deletado-->
                <button class="btn btn-sm btn-danger" @click="openModal(arquivo, 'delete')">Apagar
                    Definitivamente</button>
            </template>
        </CardImovel>
        <Modal @close="toggleModal" :modalActive="modalActive">
            <div v-if="modalDelete">
                <div v-if="invalidesOrNot">
                    <div class="modal-content" v-if="confirmation">
                        <h1>Arquivo apagado com sucesso</h1>
                    </div>
                    <div class="modal-content" v-else>
                        <h1>Deseja realmente apagar o arquivo {{ arquivoNome }}?</h1>
                        <button class="btn btn-sm btn-danger"
                            @click="apagarArquivo(arquivoId, 'Arquivo')">Apagar</button>
                    </div>
                </div>
                <div v-else>
                    <div class="modal-content" v-if="confirmation">
                        <h1>Arquivo apagado permanentemente com sucesso</h1>
                    </div>
                    <div class="modal-content" v-else>
                        <h1>Deseja realmente apagar o arquivo {{ arquivoNome }} permanentemente?</h1>
                        <button class="btn btn-sm btn-danger"
                            @click="apagarArquivo(arquivoId, 'ArquivoPermanentemente')">Apagar</button>
                    </div>
                </div>
            </div>
            <div v-else class="modal-content">
                <div v-if="invalidesOrNot">
                    <div v-if="edit">
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
                    <div v-if="confirmation && !reactiveImovel">
                        <h1>Arquivo reativado com sucesso</h1>
                    </div>
                    <div v-if="!confirmation && !reactiveImovel">
                        <h1>Deseja realmente reativar o arquivo {{ arquivoNome }}?</h1>
                        <button class="btn btn-sm btn-success" @click="reativarArquivo(arquivoId)">Reativar</button>
                    </div>
                    <div v-if="confirmation && reactiveImovel">
                        <h1>Imóvel do arquivo reativado com sucesso</h1>
                    </div>
                    <div v-if="!confirmation && reactiveImovel">
                        <h1>Deseja realmente reativar o imóvel do arquivo {{ arquivoNome }}?</h1>
                        <button class="btn btn-sm btn-primary"
                            @click="reativarImovel(arquivoImovelId)">Reativar</button>
                    </div>
                    <!-- ver de fazer essas coisas numa variável por mensagem no script-->
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

library.add(faSearch, faFilePdf, faImage, faFileWord, faFileExcel)



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
            arquivoImovelId: ''
        }
    },

    props: {
        propsSemImovel: Boolean
    },

    components: { CardImovel, Modal, FontAwesomeIcon, UploadArquivo, LoadingSection },

    setup() {
        const modalActive = ref(false);
        const toggleModal = () => {
            modalActive.value = !modalActive.value;
        };
        return { modalActive, toggleModal };
    },
    // https://stackoverflow.com/questions/53772331/vue-html-js-how-to-download-a-file-to-browser-using-the-download-tag

    methods: {
        ...mapActions(["loadArquivos", "buscaArquivo", "loadImoveisValidosEInvalidos", "loadArquivosInvalidos", "loadArquivosSemImoveis", "reativarImovel"]),

        async procuraArquivo() {
            this.$store.commit('isFetching', true);
            if (this.keywords.length == 0) {
                if (this.invalidesOrNot) {
                    await this.loadArquivos();
                }
                else {
                    await this.loadArquivosInvalidos();
                }
            }
            else {
                let status;
                if (this.invalidesOrNot) {
                    status = 'A';
                }
                else {
                    status = 'I'
                }

                let payload = { keywords: this.keywords, tipo: this.tipoBusca, status: status }

                this.$store.dispatch('buscaArquivo', payload).catch(error => console.log(error))
            }
            this.$store.commit('isFetching', false);
        },

        openModal(arquivo, tipo) {
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
                    this.edit = true;
                    if (arquivo.imovel_id) {
                        this.arquivoImovel = this.displayListaImoveis.find(x => x.id == arquivo.imovel_id).id;
                        this.$refs.formulario.imovel = this.arquivoImovel;
                    }
                    this.$refs.formulario.nome = arquivo.nome;
                }
            }
            this.toggleModal();
        },

        async apagarArquivo(id, tipo) {
            this.$store.commit('isFetching', true);
            await this.$store.dispatch('apagar' + tipo, id)
                .then(() => {
                    if (tipo == 'Arquivo') {
                        this.loadArquivos();
                    }
                    else {
                        this.loadArquivosInvalidos();
                    }
                    this.confirmation = true;
                }).catch(error => console.log(error))
            this.$store.commit('isFetching', false);

        },

        async reativarArquivo(id) {
            this.$store.commit('isFetching', true);
            await this.$store.dispatch('reativarArquivo', id)
                .then(() => {
                    this.loadArquivosInvalidos();
                    this.confirmation = true;
                }).catch(error => console.log(error))
            this.$store.commit('isFetching', false);

        },

        async reativarImovel(id) {
            this.$store.commit('isFetching', true);
            await this.$store.dispatch('reativarImovel', id)
                .then(() => {
                    this.loadArquivosInvalidos();
                    this.loadImoveisValidosEInvalidos();
                    this.confirmation = true;
                }).catch(error => console.log(error));
            this.$store.commit('isFetching', false);
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

        async onCompleteEdit() {
            let arquivo = {
                id: this.arquivoId,
                nome: this.$refs.formulario.nome,
                imovel: this.$refs.formulario.imovel,
                data_edicao: this.getNow(),
            }
            this.$store.commit('isFetching', true);

            await this.$store.dispatch('updateArquivo', arquivo)
                .then(() => {
                    if (this.semImovel) {
                        this.loadArquivosSemImoveis();
                    }
                    else {
                        this.loadArquivos();
                    }
                    this.edit = false;
                }).catch(error => console.log(error))
            this.$store.commit('isFetching', false);

        },

        async changeList() {
            this.keywords = '';
            this.$store.commit('isFetching', true);
            if (this.semImovel) {
                await this.loadArquivosSemImoveis();
            }
            else {
                if (this.invalidesOrNot) {
                    await this.loadArquivos();
                }
                else {
                    await this.loadArquivosInvalidos();
                }
            }
            this.$store.commit('isFetching', false);
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
        this.$store.commit('isFetching', true);
        await this.loadImoveisValidosEInvalidos();
        if (this.propsSemImovel) {
            this.semImovel = true;
            await this.loadArquivosSemImoveis();
        }
        else {
            await this.loadArquivos();
        }
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

.barra {
    display: flex;
}

.colors {
    background-color: #198754;
    color: white;
}

.favIcon {
    color: yellow
}

.urgIcon {
    color: red
}

.impIcon {
    color: red;
}

.margin-barra {
    margin-top: 1%;
    margin-bottom: 1%;
}
</style>