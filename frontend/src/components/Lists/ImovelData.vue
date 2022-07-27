<template>
    <div class="container" v-if="!id">
        <DeuRuim></DeuRuim>
    </div>
    <div class="container" v-else>

        <h6>{{ id }}</h6>
        {{ displayImovel }}
    </div>



</template>

<script>
import { mapGetters } from "vuex";
import DeuRuim from '../Utils/DeuRuim.vue'

export default {
    props: {
        id: Number
    },

    components: { DeuRuim, },


    computed: {
        ...mapGetters([
            "displayImovel"
        ]),

    },
}

</script>