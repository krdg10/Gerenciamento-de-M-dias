<template>
    <div class="container">
        <div class="row">
            <input class="form-control" name="busca" id="busca" placeholder="Digite sua busca" />
            <button class="btn btn-sm btn-success">Buscar</button>
        </div>
    </div>
    <div class="row">
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
                <button class="btn btn-sm btn-success">Detalhes</button>
            </template>
        </CardImovel>
    </div>
</template>

<script>
import CardImovel from "../Utils/CardImovel.vue";
import { mapActions, mapGetters } from "vuex";

// Além disso... arrumar design, fazer busca. Imovel data pra mostrar dados do imovel individualmente e editar.
export default {

    components: { CardImovel },

    methods: {
        ...mapActions(["loadImoveis"]),
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