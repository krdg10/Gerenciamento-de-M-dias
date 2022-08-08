<template>
    <div class="container d-flex justify-content-center margin-barra">
        <div class="row">
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
    <div class="container" v-if="displayListaImoveis.length == 0">
        <h4>Sem imóveis cadastrados</h4>
    </div>
    <div class="row" v-else>
        <CardImovel class="col-sm-6" v-for="imovel in displayListaImoveis" :key="imovel.id" :id="imovel.id">
            <template v-slot:card-header>

                <h3 class="card-title" style="color: #4E73DF;" @click="redirect(imovel)">{{ imovel.nome }} {{
                        imovel.id
                }}</h3>
                <font-awesome-icon icon="fa-solid fa-exclamation" class="static"
                    v-bind:class="{ 'impIcon': imovel.importante == 1 }"
                    @click="addTag(imovel.id, 'importante', imovel.importante)" />
                <font-awesome-icon icon="fa fa-star" class="static" v-bind:class="{ 'favIcon': imovel.favorito == 1 }"
                    @click="addTag(imovel.id, 'favorito', imovel.favorito)" />
                <font-awesome-icon icon="fa-solid fa-triangle-exclamation" class="static"
                    v-bind:class="{ 'urgIcon': imovel.urgente == 1 }"
                    @click="addTag(imovel.id, 'urgente', imovel.urgente)" />
            </template>
            <template v-slot:card-body>
                <strong>Cidade</strong>
                : {{ imovel.cidade }} <br><br>
                <strong>Preço</strong>
                : {{ imovel.preco }} <br><br>
            </template>
            <template v-slot:card-footer>
                <button class="btn btn-sm btn-success" @click="redirect(imovel)">Detalhes</button>
                <button class="btn btn-sm btn-danger" @click="openModal(imovel)">Apagar</button>
            </template>
        </CardImovel>
        <Modal @close="toggleModal" :modalActive="modalActive">
            <div class="modal-content" v-if="confirmation">
                <h1>Imóvel apagado com sucesso</h1>
            </div>
            <div class="modal-content" v-else>
                <h1>Deseja realmente apagar o imóvel {{ imovelNome }}? {{ imovelId }}</h1>
                <button class="btn btn-sm btn-danger" @click="apagarImovel(imovelId)">Apagar</button>
            </div>
        </Modal>

    </div>
</template>

<script>
import CardImovel from "../Utils/CardImovel.vue";
import { mapActions, mapGetters } from "vuex";

import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faSearch, faStar, faExclamation, faTriangleExclamation } from '@fortawesome/free-solid-svg-icons'
library.add(faSearch, faStar, faExclamation, faTriangleExclamation)
// adicionar isso no X do modal e tirar o link do fontawesome do index
// ver de mudar cor do icons e tornálos clicáveis pra fazer os botões de tag. 

import Modal from "../Utils/ModalDefault.vue";
import { ref } from "vue";

export default {
    data() {
        return {
            keywords: '',
            confirmation: false,
            imovelId: '',
            imovelNome: ''
        }
    },

    components: { CardImovel, Modal, FontAwesomeIcon },

    setup() {
        const modalActive = ref(false);
        const toggleModal = () => {
            modalActive.value = !modalActive.value;
        };
        return { modalActive, toggleModal };
    },

    methods: {
        ...mapActions(["loadImoveis", "buscaImovel"]),

        async procuraImovel() {
            this.$store.dispatch('buscaImovel', this.keywords)
                .then(response => {
                    console.log(response)

                }).catch(error => console.log(error))
        },

        openModal(imovel) {
            this.imovelId = imovel.id;
            this.imovelNome = imovel.nome;
            this.confirmation = false;
            this.toggleModal();

        },

        /// pensar pra ver se tem alguma forma do modal de exclusão funcionar sem os negocios do data(). Mas por enquanto a solução atual funciona

        async apagarImovel(id) {
            console.log(id);
            this.$store.dispatch('apagarImovel', id)
                .then(() => {
                    this.loadImoveis();
                    this.confirmation = true;
                }).catch(error => console.log(error))
        },

        async addTag(id, type, value) {
            console.log(id + type + value)
        },

        redirect(imovel) {
            let id = imovel.id;
            this.$store.commit('imovel', imovel);

            this.$router.push({ name: 'novoImovel', params: { id: id } })

            // passando só id via props e o resto por vuex... mas daria pra passar tudo por props passando parametro por parametro. mas seria paia.
        }
    },

    computed: {
        ...mapGetters([
            "displayListaImoveis"
        ]),

    },

    async created() {
        await this.loadImoveis();
        console.log(this.displayListaImoveis);
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