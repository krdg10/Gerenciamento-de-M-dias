<template>
    <div class="container">
        <Form @submit="onCompleteCreate" :validation-schema="schema">
            <div class="mb-3 mt-3">
                <label for="nome" class="form-label">Nome:</label>
                <Field type="text" class="form-control" id="nome" placeholder="Nome do Imóvel" name="nome"
                    v-model="nome" required />
                <ErrorMessage name="nome" />
            </div>
            <div class="mb-3">
                <label for="imovel" class="form-label">Imóvel:</label>
                <Field as="select" class="form-control" id="imovel" placeholder="Imóvel associado ao arquivo"
                    name="imovel" v-model="imovel">
                    <option value="" selected>Selecione o imóvel</option>
                    <option v-for="imovel in displayListaImoveis" :value="imovel.id" :key="imovel.id">
                        {{ imovel.id }} - {{ imovel.nome }}
                    </option>
                </Field>
            </div>
            <div class="container my-5">
                <div class="row">
                    <DropZone @drop.prevent="drop" @change="selectedFile" :file="dropzoneFile.name" ref="arquivo"
                        class="col align-self-center" v-if="!imovelProps" />
                </div>
            </div>
            <div class="container">
                <div class="row">
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary" v-if="!imovelProps">Submit</button>
                    </div>
                </div>
            </div>
        </Form>
        <slot name="button-submit"></slot>
    </div>
    <Modal @close="toggleModal" :modalActive="modalActive" :redirectToAnotherPage="$router.push"
        pageToRedirect="listaArquivos">
        <div class="modal-content">
            <h1>Arquivo Criado Com Sucesso</h1>
        </div>
    </Modal>
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
import axios from 'axios';
import Modal from "../Utils/ModalDefault.vue";


export default {
    name: 'UploadArquivo',

    data() {
        return {
            nome: '',
            imovel: ''
        }
    },

    components: {
        DropZone, Field, ErrorMessage, Form, Modal
    },

    props: {
        imovelProps: Boolean
    },

    setup() {
        const modalActive = ref(false);
        const toggleModal = () => {
            modalActive.value = !modalActive.value;
        };

        let dropzoneFile = ref("");
        const drop = (e) => {
            dropzoneFile.value = e.dataTransfer.files[0];
        };
        const selectedFile = () => {
            dropzoneFile.value = document.querySelector(".dropzoneFile").files[0];
        };
        // talvez apagar
        return { dropzoneFile, drop, selectedFile, toggleModal, modalActive };
    },

    methods: {
        ...mapActions(["loadImoveis"]),
        // https://masteringjs.io/tutorials/vue/file-upload#:~:text=Using%20Vue%202%20with%20Axios,it%20easy%20to%20upload%20files. 

        async onCompleteCreate() {

            const arquivoUploaded = document.querySelector(".dropzoneFile").files[0];
            const formData = new FormData();
            formData.append('uploadedFile', arquivoUploaded);
            formData.append('nome', this.nome,);
            formData.append('data_upload', this.getNow());
            formData.append('imovel_id', this.imovel);

            const headers = { 'Content-Type': 'multipart/form-data' };
            const allowedfileExtensions = ['jpg', 'jpeg', 'png', 'xlsx', 'xls', 'doc', 'docx', 'pdf'];
            if (!allowedfileExtensions.includes(arquivoUploaded.name.split('.')[1])) {
                console.log('Formato não aceito');
                return;
            }


            await axios({ url: 'http://localhost:8000/arquivo/novoArquivo', data: formData, method: 'POST', headers: headers })
                .then(response => {
                    console.log(response);
                    this.toggleModal();

                }).catch(error => {
                    console.log(error)
                })

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