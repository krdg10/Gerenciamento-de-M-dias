<template>
  <div class="container" v-if="!id">
    <h4>Criar Novo Imóvel</h4>
    <Form @submit="onCompleteCreate" :validation-schema="schema">
      <FormulariodeImovel ref="formulario"></FormulariodeImovel>
      <button type="submit" class="btn btn-primary">Submit</button>
    </Form>
  </div>

  <div class="container" v-else>
    <h4>Editar Imóvel</h4>
    <Form @submit="onCompleteEdit" :validation-schema="schema">
      <FormulariodeImovel ref="formulario" :imovel="displayImovel"></FormulariodeImovel>
      <button type="submit" class="btn btn-primary">Submit</button>
    </Form>
  </div>

  <Modal @close="toggleModal" :modalActive="modalActive" :redirectToAnotherPage="$router.push">
    <div class="modal-content">
      <h1 v-if="!id">Imóvel Criado Com Sucesso</h1>
      <h1 v-else>Imóvel Editado Com Sucesso</h1>
    </div>
  </Modal>
</template>


<script>
import { mapGetters } from 'vuex'
import { Form } from 'vee-validate';
import FormulariodeImovel from '../Utils/FormulariodeImovel.vue'
import Modal from "../Utils/ModalDefault.vue";
import { ref } from "vue";


export default {

  name: 'FormularioImovel',

  components: { Form, FormulariodeImovel, Modal },

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
      await this.$store.dispatch('createImovel', imovel)
        .then(response => {
          this.toggleModal();
          console.log(response.data)
        }).catch(error => console.log(error))
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
      await this.$store.dispatch('updateImovel', imovel)
        .then(response => {
          this.toggleModal();
          console.log(response.data)
        }).catch(error => console.log(error))
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