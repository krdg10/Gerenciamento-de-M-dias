<template>
    <div class="container">
        <Form @submit="onCompleteCreate" :validation-schema="schema">
            <h1>DropZone</h1>
            <div class="mb-3 mt-3">
                <label for="nome" class="form-label">Nome:</label>
                <Field type="text" class="form-control" id="nome" placeholder="Nome do Imóvel" name="nome"
                    v-model="nome" required />
                <ErrorMessage name="nome" />
            </div>
            <div class="mb-3 mt-3">
                <label for="imovel" class="form-label">Imóvel:</label>
                <Field as="select" class="form-control" id="imovel" placeholder="Imóvel associado ao arquivo"
                    name="imovel" v-model="imovel">
                    <option value="" selected>Selecione o imóvel</option>
                    <option v-for="imovel in displayListaImoveis" :value="imovel.id" :key="imovel.id">
                        {{ imovel.id }} - {{ imovel.nome }}
                    </option>
                </Field>
            </div>

            <DropZone @drop.prevent="drop" @change="selectedFile" :file="dropzoneFile.name" />

            <button type="submit" class="btn btn-primary">Submit</button>
        </Form>
    </div>
</template>

<script>
// https://www.youtube.com/watch?v=wWKhKPN_Pmw 
// peguei o dropzone disso ai... mas dar uma procurada e tentar esse pack
// https://www.npmjs.com/package/vue3-dropzone
// também procurar coisa sobre paginação. tomar decisão se paginação vai vir do back/php ou se vai ser só no vue. Acho que só no vue é mais fácil
// mas nao quero caminho fácil nesse caso

import DropZone from "../Utils/Dropzone.vue";
import { Form } from 'vee-validate';
import { ref } from "vue";
import { Field, ErrorMessage } from 'vee-validate';
import '../../assets/validations';
import { mapGetters, mapActions } from "vuex";


export default {
    data() {
        return {
            nome: '',
            imovel: ''
        }
    },

    components: {
        DropZone, Field, ErrorMessage, Form
    },

    setup() {
        let dropzoneFile = ref("");
        const drop = (e) => {
            dropzoneFile.value = e.dataTransfer.files[0];
        };
        const selectedFile = () => {
            dropzoneFile.value = document.querySelector(".dropzoneFile").files[0];
        };
        return { dropzoneFile, drop, selectedFile };
    },

    methods: {
        ...mapActions(["loadImoveis"]),


        /// fazer pagina das midias. pesquisar como faz upload tanto no vue quanto no php.
        // pesquisar sobre paginação no php

        async onCompleteCreate() {
           
            let arquivo = {
                nome: this.nome,
                data_upload: this.getNow(),
                imovel_id: this.imovel
            }
            console.log(arquivo);
        },

        getNow() {
            const today = new Date();
            const date = today.getFullYear() + '-' + (today.getMonth() + 1) + '-' + today.getDate();
            const time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
            const dateTime = date + ' ' + time;
            return dateTime;
        }
    },

    computed: {
        ...mapGetters([
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
        await this.loadImoveis();
    },
};
</script>

