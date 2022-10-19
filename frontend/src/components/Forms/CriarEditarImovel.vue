<template>
  <div class="container" v-if="!id">
    <h4 class="my-3 text-center">Criar Novo Imóvel</h4>
    <Form @submit="onCompleteCreate" :validation-schema="schema">
      <FormulariodeImovel ref="formulario"></FormulariodeImovel>
      <div class="container my-5">
        <div class="row">
          <div class="text-center">
            <button type="submit" class="btn btn-primary">Submit</button>
          </div>
        </div>
      </div>
    </Form>
  </div>

  <div class="container" v-else>
    <h4 class="my-3 text-center">Editar Imóvel</h4>
    <Form @submit="onCompleteEdit" :validation-schema="schema">
      <FormulariodeImovel ref="formulario" :imovel="displayImovel"></FormulariodeImovel>
      <div class="container my-5">
        <div class="row">
          <div class="text-center">
            <button type="submit" class="btn btn-primary">Submit</button>
          </div>
        </div>
      </div>
    </Form>
  </div>

  <Modal @close="toggleModal" :modalActive="modalActive" :redirectToAnotherPage="$router.push"
    pageToRedirect="listaImoveis" :showCloseButton="true" :redirectOrNot="redirectOrNot">
    <div class="modal-content">
      <h1>{{modalMessage}}</h1>
    </div>
  </Modal>
</template>


<script>
import { mapGetters } from 'vuex'
import { Form } from 'vee-validate';
import FormulariodeImovel from '../Utils/FormulariodeImovel.vue'
import Modal from "../Utils/ModalDefault.vue";
import { ref } from "vue";
import axios from 'axios';

export default {

  name: 'FormularioImovel',
  components: { Form, FormulariodeImovel, Modal },
  data() {
    return {
      modalMessage: '',
      redirectOrNot: false
    }
  },

  setup() {
    const modalActive = ref(false);
    const toggleModal = () => {
      modalActive.value = !modalActive.value;
    };
    return { modalActive, toggleModal };
  },

  props: {
    id: String
  },

  methods: {
    async onCompleteCreate() {
      let imovel = {
        nome: this.$refs.formulario.nome,
        descricao: this.$refs.formulario.descricao,
        preco: this.$refs.formulario.preco,
        cep: this.$refs.formulario.cep,
        rua: this.$refs.formulario.rua,
        bairro: this.$refs.formulario.bairro,
        numero: this.$refs.formulario.numero,
        cidade: this.$refs.formulario.cidade,
        estado: this.$refs.formulario.estado,
        complemento: this.$refs.formulario.complemento,
        data_cadastro: this.getNow(),
      }
      await axios({ url: 'http://localhost:8000/imovel/' + 'novo', data: imovel, method: 'POST' })
        .then(() => {
          this.redirectOrNot = true;
          this.modalMessage = 'Imóvel Criado com Sucesso!';
          this.toggleModal();
        }).catch(error => {
          this.redirectOrNot = false;
          this.modalMessage = error.response.data;
          this.toggleModal();
        })
    },

    async onCompleteEdit() {

      let imovel = {
        id: this.id,
        nome: this.$refs.formulario.nome,
        descricao: this.$refs.formulario.descricao,
        preco: this.$refs.formulario.preco,
        cep: this.$refs.formulario.cep,
        rua: this.$refs.formulario.rua,
        bairro: this.$refs.formulario.bairro,
        numero: this.$refs.formulario.numero,
        cidade: this.$refs.formulario.cidade,
        estado: this.$refs.formulario.estado,
        complemento: this.$refs.formulario.complemento,
        data_edicao: this.getNow(),
      }

      await axios({ url: 'http://localhost:8000/imovel/' + 'editar' + '/' + imovel.id, data: imovel, method: 'PUT' })
        .then(() => {
          const payload = imovel;
          this.$store.commit('imovel', payload);
          this.redirectOrNot = true;
          this.modalMessage = 'Imóvel Editado com Sucesso!';
          this.toggleModal();
        }).catch(error => {
          this.redirectOrNot = false;
          this.modalMessage = Object.values(error.response.data[0])[0][0];
          this.toggleModal();
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
      'displayImovel'
    ]),

    schema() {
      const simpleSchema = {
        nome: 'required|max:50',
        descricao: 'required|max:500',
        preco: 'required|numeric|min_value:1',
        cep: 'required|numeric|digits:8',
        rua: 'required|max:50',
        bairro: 'required|max:50',
        numero: 'max:50',
        cidade: 'required|max:50',
        estado: 'required|max:2',
        complemento: 'max:50'
      }
      return simpleSchema;
    },
  },
}
</script>