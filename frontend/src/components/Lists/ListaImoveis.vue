<template>
    <div class="container">
        <div class="row">
            <input class="form-control" name="busca" id="busca" placeholder="Digite sua busca" v-model="keywords" />
            <button class="btn btn-sm btn-success" @click="procuraImovel">Buscar</button>
        </div>
    </div>
    <div class="container" v-if="displayListaImoveis.length == 0">
        <h4>Sem imóveis cadastrados</h4>
    </div>
    <div class="row" v-else>
        <CardImovel class="col-sm-6" v-for="imovel in displayListaImoveis" :key="imovel.id" :id="imovel.id">
            <template v-slot:card-header>
                <h3 class="card-title" style="color: #4E73DF;">{{ imovel.nome }} </h3>
            </template>
            <template v-slot:card-body>
                <strong>Cidade</strong>
                : {{ imovel.cidade }} <br><br>
                <strong>Preço</strong>
                : {{ imovel.preco }} <br><br>
            </template>
            <template v-slot:card-footer>
                <button class="btn btn-sm btn-success" @click="redirect(imovel)">Detalhes</button>
                <button class="btn btn-sm btn-danger" @click="toggleModal">Apagar</button>
                <!--<button class="btn btn-sm btn-danger" @click="apagarImovel(imovel.id)">Apagar</button>-->
                <Modal @close="toggleModal" :modalActive="modalActive">
                    <div class="modal-content">
                        <h1>Deseja realmente apagar o imóvel?</h1>
                        <button class="btn btn-sm btn-danger" @click="apagarImovel(imovel.id)">Apagar</button>
                    </div>
                </Modal>
            </template>


        </CardImovel>


    </div>
</template>

<script>
import CardImovel from "../Utils/CardImovel.vue";
import { mapActions, mapGetters } from "vuex";


import Modal from "../Utils/ModalDefault.vue";
import { ref } from "vue";
/// https://mdbootstrap.com/docs/vue/components/modal/
// proxima ação: fazer modal generico e usar aqui pra confirmar delete. talvez usar link acima mas de preferencia não
// https://www.youtube.com/watch?v=NFdvWBh-D6k esse video aqui pode ser uma boa. mais que o link de cima. mas puta preguiça agora...
// mas ver ele inteiro pra ver as explicações e os carai
export default {
    data() {
        return {
            keywords: '',
        }
    },

    components: { CardImovel, Modal },

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

        async apagarImovel(id) {
            this.$store.dispatch('apagarImovel', id)
                .then(() => {
                    this.loadImoveis();
                    this.toggleModal();

                }).catch(error => console.log(error))
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