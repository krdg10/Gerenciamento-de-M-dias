<template>
  <div class="container" v-if="!id">
    <h4>Criar Novo Imóvel</h4>
    <Form @submit="onComplete" :validation-schema="schema">
      <FormulariodeImovel ref="formulario"></FormulariodeImovel>
      <button type="submit" class="btn btn-primary">Submit</button>
    </Form>
  </div>

  <div class="container" v-else>
    <h4>Editar Imóvel</h4>
    <Form :validation-schema="schema">
      <FormulariodeImovel :imovel="displayImovel"></FormulariodeImovel>
      <button type="submit" class="btn btn-primary">Submit</button>
    </Form>

  </div>
</template>


<script>
import { mapGetters } from 'vuex'
import { Form } from 'vee-validate';
import FormulariodeImovel from '../Utils/FormulariodeImovel.vue'

export default {

  name: 'FormularioImovel',

  components: { Form, FormulariodeImovel },

  props: {
    id: String
  },

  methods: {
    async onComplete() {

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