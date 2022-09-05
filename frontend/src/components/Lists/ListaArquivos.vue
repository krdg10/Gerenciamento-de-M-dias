<template>
    <div class="container d-flex justify-content-center margin-barra my-3">
        <div class="row">
            <div class="input-group paddingZeroLeft margin-bottom-40">
                <div class="input-group-btn barra container">
                    <div class="row">
                        <div class="col-4 paddingZero">
                            <select class="form-control" name="tipoBusca" id="tipoBusca" v-model="tipoBusca"
                                @change="keywords = ''">
                                <option value="nome" selected>Nomes</option>
                                <option value="imovel">Imovel</option>
                            </select>
                        </div>
                        <div class="col-7 paddingZero">
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
                        <div class="col-1 paddingZero">
                            <span>
                                <button type="submit" class="btn colors">
                                    <font-awesome-icon icon="fa-solid fa-search" @click="procuraArquivo()" />
                                </button>
                            </span>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container" v-if="displayListaArquivos.length == 0">
        <h4>Sem arquivos cadastrados</h4>
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
                    v-if="arquivo.imovel_id"><strong>Imovel
                        Associado</strong>: {{
                                displayImovelById(arquivo.imovel_id).nome
                        }} </a>
                <div v-else><strong>Associado</strong>: Nenhum</div>
                <strong>Tipo do Arquivo</strong>: {{ arquivo.caminho.split(".")[1] }} <br><br>

            </template>
            <template v-slot:card-footer>
                <button class="btn btn-sm btn-success" @click="openModalEditar(arquivo)">Editar</button>
                <button class="btn btn-sm btn-danger" @click="openModalApagar(arquivo)">Apagar</button>
            </template>
        </CardImovel>
        <Modal @close="toggleModal" :modalActive="modalActive">
            <div v-if="modalDelete">
                <div class="modal-content" v-if="confirmation">
                    <h1>Arquivo apagado com sucesso</h1>
                </div>
                <div class="modal-content" v-else>
                    <h1>Deseja realmente apagar o arquivo {{ arquivoNome }}?</h1>
                    <button class="btn btn-sm btn-danger" @click="apagarArquivo(arquivoId)">Apagar</button>
                </div>
            </div>
            <div v-else class="modal-content">
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
        </Modal>

    </div>
</template>

<script>
import CardImovel from "../Utils/CardImovel.vue";
import { mapActions, mapGetters } from "vuex";
import Modal from "../Utils/ModalDefault.vue";
import { ref } from "vue";
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faSearch, faFilePdf, faImage, faFileWord, faFileExcel } from '@fortawesome/free-solid-svg-icons'
import UploadArquivo from '../Forms/UploadArquivos.vue'
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
            edit: false,
            imovelProps: true,
            tipoBusca: 'nome',
            baseUrl: 'http://localhost:8000/',
            imageTypes: ['png', 'jpg', 'jpeg']
        }
    },

    components: { CardImovel, Modal, FontAwesomeIcon, UploadArquivo },

    setup() {
        const modalActive = ref(false);
        const toggleModal = () => {
            modalActive.value = !modalActive.value;
        };
        return { modalActive, toggleModal };
    },
    // https://stackoverflow.com/questions/53772331/vue-html-js-how-to-download-a-file-to-browser-using-the-download-tag

    methods: {
        ...mapActions(["loadArquivos", "buscaArquivo", "loadImoveis"]),

        async procuraArquivo() {
            if (this.keywords.length == 0) {
                await this.loadArquivos();
            }
            else {
                let payload = { keywords: this.keywords, tipo: this.tipoBusca }

                this.$store.dispatch('buscaArquivo', payload)
                    .then(response => {
                        console.log(response)

                    }).catch(error => console.log(error))
            }

        },

        openModalApagar(arquivo) {
            this.arquivoId = arquivo.id;
            this.arquivoNome = arquivo.nome;
            this.confirmation = false;
            this.modalDelete = true;
            this.toggleModal();
        },

        async openModalEditar(arquivo) {
            this.edit = true;
            this.modalDelete = false;
            this.arquivoNome = arquivo.nome;
            await this.loadImoveis();
            this.arquivoImovel = this.displayListaImoveis.find(x => x.id == arquivo.imovel_id).id;
            this.arquivoId = arquivo.id;
            this.$refs.formulario.nome = arquivo.nome;
            this.$refs.formulario.imovel = this.arquivoImovel;
            this.toggleModal();
        },

        /// pensar pra ver se tem alguma forma do modal de exclusão funcionar sem os negocios do data(). Mas por enquanto a solução atual funciona

        async apagarArquivo(id) {
            console.log(id);
            this.$store.dispatch('apagarArquivo', id)
                .then(() => {
                    this.loadArquivos();
                    this.confirmation = true;
                }).catch(error => console.log(error))
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

            await this.$store.dispatch('updateArquivo', arquivo)
                .then(() => {
                    this.loadArquivos();
                    this.edit = false;
                }).catch(error => console.log(error))
        },

        edirect(imovel) {
            let id = imovel.id;
            this.$store.commit('imovel', imovel);
            this.$router.push({ name: 'novoImovel', params: { id: id } })
            // passando só id via props e o resto por vuex... mas daria pra passar tudo por props passando parametro por parametro. mas seria paia.
        }
    },

    computed: {
        ...mapGetters([
            "displayListaArquivos",
            "displayListaImoveis",
            "displayImovelById"
        ]),

        schema() {
            const simpleSchema = {
                nome: 'required|max:50'
            }
            return simpleSchema;
        },

    },

    async created() {
        await this.loadImoveis();
        await this.loadArquivos();
        console.log(this.displayListaArquivos);
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