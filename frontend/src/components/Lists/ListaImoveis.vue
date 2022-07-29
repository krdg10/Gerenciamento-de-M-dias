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
            </template>
        </CardImovel>
    </div>
</template>

<script>
import CardImovel from "../Utils/CardImovel.vue";
import { mapActions, mapGetters } from "vuex";

// Além disso... arrumar design, fazer busca. Imovel data pra mostrar dados do imovel individualmente e editar.
export default {
    data() {
        return {
            keywords: '',
        }
    },

    components: { CardImovel },

    methods: {
        ...mapActions(["loadImoveis", "buscaImovel"]),

        async procuraImovel() {
            this.$store.dispatch('buscaImovel', this.keywords)
                .then(response => {
                    console.log(response)

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