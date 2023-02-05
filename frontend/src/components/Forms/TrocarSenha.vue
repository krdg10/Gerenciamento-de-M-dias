<template>
    <div class="container">
        <h4 class="my-3 text-center">Trocar Senha</h4>

        <Form @submit="editPassword" :validation-schema="schema">
            <div class="mb-3 mt-3">
                <label for="oldPassword" class="form-label">Senha Atual:</label>
                <Field type="password" class="form-control" id="oldPassword" placeholder="Senha Atual"
                    name="oldPassword" v-model="oldPassword" required />
                <ErrorMessage name="oldPassword" />
            </div>
            <div class="mb-3 mt-3">
                <label for="newPassword" class="form-label">Nova Senha:</label>
                <Field type="password" class="form-control" id="newPassword" placeholder="Nova Senha" name="newPassword"
                    v-model="newPassword" required />
                <ErrorMessage name="newPassword" />
            </div>

            <div class="container">
                <div class="row">
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </div>
        </Form>

        <div class="container">
            <div class="text-center">
                <button type="submit" class="btn btn-danger">Apagar Conta</button>
            </div>
        </div>


    </div>
    <Modal @close="toggleModal" :modalActive="modalActive" :redirectToAnotherPage="$router.push"
        pageToRedirect="listaArquivos" :showCloseButton="true" :redirectOrNot="redirectOrNot">
        <div class="modal-content">
            <h1>{{ modalMessage }}</h1>
        </div>
    </Modal>
</template>

<script>
import { Form } from 'vee-validate';
import { Field, ErrorMessage } from 'vee-validate';
import '../../assets/validations';
import axios from 'axios';
import Modal from "../Utils/ModalDefault.vue";
import { ref } from "vue";

export default {
    data() {
        return {
            oldPassword: '',
            newPassword: '',
            modalMessage: '',
            redirectOrNot: false,
        }
    },


    setup() {
        const modalActive = ref(false);
        const toggleModal = () => {
            modalActive.value = !modalActive.value;
        };
        return { modalActive, toggleModal };
    },

    components: {
        Field, ErrorMessage, Form, Modal
    },

    methods: {
        async editPassword() {
            let payload = {
                newPassword: this.newPassword,
                password: this.oldPassword,
            }

            const headers = {
                "Authorization": "Bearer " + this.$store.state.login.token,
            };

            await axios({ url: 'http://localhost:8000/user/editPassword', data: payload, headers: headers, method: 'PUT' })
                .then(response => {
                    this.newPassword = '';
                    this.oldPassword = '';
                    this.modalMessage = response.data.message;
                    this.toggleModal();
                }).catch(error => {
                    this.newPassword = '';
                    this.oldPassword = '';
                    this.modalMessage = error.response.data;
                    this.toggleModal()
                })
        }
        // edit password.
        // deletar conta
        // edit password acho que tá suave o back, deleter vou ter que refazer.
        // edit password pega as duas senhas, valida se as duas senhas estão lá, se tiver, compara as duas. acho que da pra fazer isso com o vee, pega o token e o email do token
        // delete só pega o token e faz os trabalhos se o token for valido e n for master
    },

    computed: {
        schema() {
            const simpleSchema = {
                oldPassword: 'required|min:8|max:50',
                newPassword: 'required|min:8|max:50',
            }
            return simpleSchema;
        },
    },

}
</script>
