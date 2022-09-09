<template>
    <div class="container-fluid">
        <h2 class="text-center my-3">Dashboard</h2>
        <div class="row">
            <CardImovel class="border-left-warning shadow h-100 col-sm-6">
                <template v-slot:card-header>
                    <router-link to="/listaImoveis" class="color-black">
                        <h3>Imoveis</h3>
                    </router-link>
                </template>
                <template v-slot:card-body>
                    <strong>Imóveis Ativos:</strong> {{ displayQuantidadeDeImoveisAtivos }} <br>
                    <strong>Imóveis Inativos:</strong> {{ displayQuantidadeDeImoveisInativos }} <br>
                </template>
                <template v-slot:card-footer>
                    <router-link to="/listaImoveis" class="btn btn-lg btn-warning">Ver Mais<span
                            class="fa fa-eye"></span>
                    </router-link>
                </template>
            </CardImovel>
            <CardImovel class="border-left-danger shadow h-100 col-sm-6">
                <template v-slot:card-header>
                    <router-link to="/listaArquivos" class="color-black">
                        <h3>Arquivos</h3>
                    </router-link>
                </template>
                <template v-slot:card-body>
                    <strong>Arquivos Ativos:</strong> {{ displayQuantidadeDeArquivossAtivos }} <br>
                    <strong>Arquivos Inativos:</strong> {{ displayQuantidadeDeArquivosInativos }} <br>
                </template>
                <template v-slot:card-footer>
                    <router-link to="/listaArquivos" class="btn btn-lg btn-danger">Ver Mais<span
                            class="fa fa-eye"></span>
                    </router-link>
                </template>
            </CardImovel>
            <CardImovel class="border-left-primary shadow h-100 col-sm-12">
                <template v-slot:card-header>
                    <router-link :to="{ name: 'listaArquivos', params: {propsSemImovel: true } }" class="color-black">
                        <h3>Arquivos Ativos Sem Imóvel Associado</h3>
                    </router-link>

                </template>
                <template v-slot:card-body>
                    <h2 class="text-center my-3">{{ displayQuantidadeDeArquivosSemImovel }}</h2>
                </template>
                <template v-slot:card-footer>
                    <router-link :to="{ name: 'listaArquivos', params: {propsSemImovel: true } }"
                        class="btn btn-lg btn-primary">Ver Mais<span class="fa fa-eye"></span>
                    </router-link>
                </template>
            </CardImovel>
        </div>
    </div>
</template>

<script>
import CardImovel from "../Utils/CardImovel.vue";
import { mapActions, mapGetters } from "vuex";


export default {
    components: { CardImovel },

    computed: {
        ...mapGetters([
            "displayQuantidadeDeImoveisAtivos",
            "displayQuantidadeDeImoveisInativos",
            "displayQuantidadeDeArquivossAtivos",
            "displayQuantidadeDeArquivosInativos",
            "displayQuantidadeDeArquivosSemImovel"
        ]),
    },
    methods: {
        ...mapActions([
            "loadQuantidadeImoveisInativos",
            "loadQuantidadeImoveisAtivos",
            "loadQuantidadeArquivosInativos",
            "loadQuantidadeArquivosAtivos",
            "loadQuantidadeArquivosSemImovel"
        ]),
    },

    async created() {
        await this.loadQuantidadeImoveisInativos();
        await this.loadQuantidadeImoveisAtivos();
        await this.loadQuantidadeArquivosInativos();
        await this.loadQuantidadeArquivosAtivos();
        await this.loadQuantidadeArquivosSemImovel();
    }


}
</script>

