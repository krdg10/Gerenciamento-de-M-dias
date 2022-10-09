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
                    <option v-for="imovel in displayListaImoveisAtivos" :value="imovel.id" :key="imovel.id">
                        {{ imovel.id }} - {{ imovel.nome }}
                    </option>
                </Field>
            </div>
            <div class="container my-5" v-if="!imovelProps">
                <div class="row">
                    <DropZone @drop.prevent="drop" @change="selectedFile" :file="dropzoneFile.name" ref="arquivo"
                        class="col align-self-center" />
                </div>
            </div>
            <div class="container" v-if="!imovelProps">
                <div class="row">
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </div>
        </Form>
        <slot name="button-submit"></slot>
    </div>
    <Modal @close="toggleModal" :modalActive="modalActive" :redirectToAnotherPage="$router.push"
        pageToRedirect="listaArquivos" :showCloseButton="true">
        <div class="modal-content">
            <h1>Arquivo Criado Com Sucesso</h1>
        </div>
    </Modal>
</template>

<script>
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

        return { dropzoneFile, drop, selectedFile, toggleModal, modalActive };
    },

    methods: {
        ...mapActions(["loadImoveis"]),

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
                .then(() => {
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
            "displayListaImoveisAtivos"
        ]),

        schema() {
            const simpleSchema = {
                nome: 'required|max:50'
            }
            return simpleSchema;
        },
    },

    async created() {
        if (!this.imovelProps) {
            await this.loadImoveis();
        }
    },
};
</script>