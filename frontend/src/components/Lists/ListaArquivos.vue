<template>
    <div class="container d-flex justify-content-center margin-barra">
        <div class="row">
            <div class="input-group margin-bottom-40">
                <div class="input-group-btn barra">
                    <input class="form-control" name="busca" id="busca" placeholder="Digite sua busca"
                        v-model="keywords" />
                    <span>
                        <button type="submit" class="btn colors">
                            <font-awesome-icon icon="fa-solid fa-search" @click="procuraArquivo()" />
                        </button>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="container" v-if="displayListaArquivos.length == 0">
        <h4>Sem arquivos cadastrados</h4>
    </div>
    <div class="row" v-else>
        <CardImovel class="col-sm-6" v-for="arquivo in displayListaArquivos" :key="arquivo.id" :id="arquivo.id">
            <template v-slot:card-header>
                <h3 class="card-title" style="color: #4E73DF;"></h3>
            </template>
            <template v-slot:card-body>
                {{ arquivo }}
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
                    <h1>Deseja realmente apagar o arquivo {{ arquivoNome }}? {{ arquivoId }}</h1>
                    <button class="btn btn-sm btn-danger" @click="apagarArquivo(arquivoId)">Apagar</button>
                </div>
            </div>
            <div v-else class="modal-content">
                <Form @submit="onCompleteEdit()" :validation-schema="schema" v-if="edit">
                    <div class="mb-3 mt-3">
                        <label for="nome" class="form-label">Nome:</label>
                        <Field type="text" class="form-control" id="nome" placeholder="Nome do Arquivo" name="nome"
                            v-model="arquivoNome" required />
                        <ErrorMessage name="nome" />
                    </div>
                    <div class="mb-3 mt-3">
                        <label for="imovel" class="form-label">Imóvel:</label>
                        <Field as="select" class="form-control" id="imovel" placeholder="Imóvel associado ao arquivo"
                            name="imovel" v-model="arquivoImovel">
                            <option v-for="imovel in displayListaImoveis" :value="imovel.id" :key="imovel.id">
                                {{ imovel.id }} - {{ imovel.nome }}
                            </option>
                        </Field>
                    </div>

                    <button type="submit" class="btn btn-primary">Submit</button>
                </Form>
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
import { Form, Field, ErrorMessage } from 'vee-validate';
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faSearch } from '@fortawesome/free-solid-svg-icons'
library.add(faSearch)

export default {
    data() {
        return {
            keywords: '',
            confirmation: false,
            arquivoId: '',
            arquivoNome: '',
            modalDelete: false,
            arquivoImovel: '',
            edit: false
        }
    },

    components: { CardImovel, Modal, FontAwesomeIcon, Field, ErrorMessage, Form, },

    setup() {
        const modalActive = ref(false);
        const toggleModal = () => {
            modalActive.value = !modalActive.value;
        };
        return { modalActive, toggleModal };
    },

    methods: {
        ...mapActions(["loadArquivos", "buscaArquivo", "loadImoveis"]),

        async procuraArquivo() {
            if (this.keywords.length == 0) {
                await this.loadArquivos();
            }
            else {
                this.$store.dispatch('buscaArquivo', this.keywords)
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
                nome: this.arquivoNome,
                imovel: this.arquivoImovel,
                data_edicao: this.getNow(),
            }

            await this.$store.dispatch('updateArquivo', arquivo)
                .then(() => {
                    this.loadArquivos();
                    this.edit = false;
                }).catch(error => console.log(error))
        },
    },

    computed: {
        ...mapGetters([
            "displayListaArquivos",
            "displayListaImoveis"
        ]),

        schema() {
            const simpleSchema = {
                nome: 'required|max:50'
            }
            return simpleSchema;
        },

    },

    async created() {
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