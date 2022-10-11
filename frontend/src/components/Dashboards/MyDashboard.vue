<template>
    <LoadingSection v-if="isFetching"></LoadingSection>
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
                    <router-link v-if="displayQuantidadeDeArquivosSemImovel>0"
                        :to="{ name: 'listaArquivos', params: {propsSemImovel: true } }" class="color-black">
                        <h3>Arquivos Ativos Sem Imóvel Associado</h3>
                    </router-link>
                    <h3 v-else class="color-black color-black-without-hover">Arquivos Ativos Sem Imóvel Associado</h3>


                </template>
                <template v-slot:card-body>
                    <h2 class="text-center my-3">{{ displayQuantidadeDeArquivosSemImovel }}</h2>
                </template>
                <template v-slot:card-footer>
                    <router-link v-if="displayQuantidadeDeArquivosSemImovel>0"
                        :to="{ name: 'listaArquivos', params: {propsSemImovel: true } }" class="btn btn-lg btn-primary">
                        Ver Mais<span class="fa fa-eye"></span>
                    </router-link>
                </template>
            </CardImovel>
        </div>
    </div>
</template>

<script>
import CardImovel from "../Utils/CardImovel.vue";
import { mapActions, mapGetters, mapState } from "vuex";
import LoadingSection from "../Utils/LoadingSection.vue";


export default {
    components: { CardImovel, LoadingSection },

    computed: {
        ...mapGetters([
            "displayQuantidadeDeImoveisAtivos",
            "displayQuantidadeDeImoveisInativos",
            "displayQuantidadeDeArquivossAtivos",
            "displayQuantidadeDeArquivosInativos",
            "displayQuantidadeDeArquivosSemImovel"
        ]),

        ...mapState([
            "isFetching",
        ]),
    },
    methods: {
        ...mapActions([
            "loadQuantidadeImoveis",
            "loadQuantidadeArquivos",
        ]),
    },

    async created() {
        this.$store.commit('isFetching', { status: true, message: 'Carregando...' });
        await this.loadQuantidadeImoveis('Ativos');
        await this.loadQuantidadeImoveis('Inativos');
        await this.loadQuantidadeArquivos('Inativos');
        await this.loadQuantidadeArquivos('Ativos');
        await this.loadQuantidadeArquivos('SemImovel');
        this.$store.commit('isFetching', { status: false, message: '' });
    }
}
</script>

