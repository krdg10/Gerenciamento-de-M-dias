<template>
    <div class="container">
        <div class="row">
            <div class="col-md-6 col-5">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault2"
                        v-model="login" @change="!login">
                </div>
            </div>
            <div class="col-md-6 col-5">
                <h1 v-if="login">Login</h1>
                <h1 v-else>New User</h1>
            </div>
        </div>
    </div>
    <div class="container">
        <Form @submit="onCompleteLogin" :validation-schema="schema">
            <div class="mb-3 mt-3">
                <label for="email" class="form-label">Email:</label>
                <Field type="email" class="form-control" id="email" placeholder="Email" name="email" v-model="email"
                    required />
                <ErrorMessage name="email" />
            </div>
            <div class="mb-3 mt-3">
                <label for="password" class="form-label">Password:</label>
                <Field type="password" class="form-control" id="password" placeholder="Password" name="password"
                    v-model="password" required />
                <ErrorMessage name="password" />
            </div>

            <div class="container">
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
import { ref } from "vue";
import axios from 'axios';
import Modal from "../Utils/ModalDefault.vue";

export default {
    data() {
        return {
            email: '',
            password: '',
            modalMessage: '',
            redirectOrNot: false,
            login: true
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
        async onCompleteLogin() {
            let login = {
                email: this.email,
                password: this.password,
            }
            var url;
            if (this.login) {
                url = 'http://localhost:8000/user/login';
            }
            else {
                url = 'http://localhost:8000/user/newUser';

            }
            await axios({ url: url, data: login, method: 'POST' })
                .then(response => {
                    this.modalMessage = 'Login realizado com sucesso!';
                    this.toggleModal();
                    this.$store.commit('isLoggedIn', { type: response.data.type, token: response.data.token });
                }).catch(error => {
                    this.modalMessage = error.response.data;
                    this.toggleModal();
                })
        }
    },

    computed: {
        schema() {
            const simpleSchema = {
                email: 'required|email',
                password: 'required|min:8',
            }
            return simpleSchema;
        },
    },

}
</script>