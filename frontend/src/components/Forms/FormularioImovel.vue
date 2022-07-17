<template>
  <div class="mb-3 mt-3">
    <label for="nome" class="form-label">Nome:</label>
    <input type="text" class="form-control" id="nome" placeholder="Nome do Imóvel" name="nome" v-model="nome">
  </div>
  <div class="mb-3">
    <label for="descricao" class="form-label">Descrição:</label>
    <textarea type="text" class="form-control" id="descricao" placeholder="Descrição do imóvel" name="descricao"
      v-model="descricao"></textarea>
  </div>
  <div class="mb-3">
    <label for="preco" class="form-label">Preço:</label>
    <input type="text" class="form-control" id="preco" placeholder="Preço do Imóvel" name="preco" v-model="preco">
  </div>
  <div class="mb-3">
    <label for="cep" class="form-label">CEP:</label>
    <input type="text" class="form-control" id="cep" placeholder="CEP do imóvel" name="cep" v-model="cep"
      @keyup="searchCep()">
  </div>
  <div class="mb-3">
    <label for="rua" class="form-label">Rua:</label>
    <input type="text" class="form-control" id="rua" placeholder="Rua do imóvel" name="rua" v-model="rua">
  </div>
  <div class="mb-3">
    <label for="bairro" class="form-label">Bairro:</label>
    <input type="text" class="form-control" id="bairro" placeholder="Bairro do imóvel" name="bairro" v-model="bairro">
  </div>
  <div class="mb-3">
    <label for="numero" class="form-label">Número:</label>
    <input type="text" class="form-control" id="numero" placeholder="Número do imóvel" name="numero" v-model="numero">
  </div>
  <div class="mb-3">
    <label for="cidade" class="form-label">Cidade:</label>
    <input type="text" class="form-control" id="cidade" placeholder="Cidade do imóvel" name="cidade" v-model="cidade">
  </div>
  <div class="mb-3">
    <label for="estado" class="form-label">Estado:</label>
    <select class="form-control" id="estado" placeholder="Estado do imóvel" name="estado" v-model="estado">
      <option value="" disabled selected>Selecione o estado do imóvel</option>
      <option value="AC">Acre</option>
      <option value="AL">Alagoas</option>
      <option value="AP">Amapá</option>
      <option value="AM">Amazonas</option>
      <option value="BA">Bahia</option>
      <option value="CE">Ceará</option>
      <option value="DF">Distrito Federal</option>
      <option value="ES">Espírito Santo</option>
      <option value="GO">Goiás</option>
      <option value="MA">Maranhão</option>
      <option value="MT">Mato Grosso</option>
      <option value="MS">Mato Grosso do Sul</option>
      <option value="MG">Minas Gerais</option>
      <option value="PA">Pará</option>
      <option value="PB">Paraíba</option>
      <option value="PR">Paraná</option>
      <option value="PE">Pernambuco</option>
      <option value="PI">Piauí</option>
      <option value="RJ">Rio de Janeiro</option>
      <option value="RN">Rio Grande do Norte</option>
      <option value="RS">Rio Grande do Sul</option>
      <option value="RO">Rondônia</option>
      <option value="RR">Roraima</option>
      <option value="SC">Santa Catarina</option>
      <option value="SP">São Paulo</option>
      <option value="SE">Sergipe</option>
      <option value="TO">Tocantins</option>
      <option value="EX">Estrangeiro</option>
    </select>
  </div>
  <div class="mb-3">
    <label for="complemento" class="form-label">Complemento:</label>
    <input type="text" class="form-control" id="complemento" placeholder="Complemento do imóvel" name="complemento"
      v-model="complemento">
  </div>
  <button type="submit" class="btn btn-primary" @click="onComplete">Submit</button>
</template>
<script>
import axios from 'axios'
//import { mapActions } from 'vuex'
import { mapGetters } from 'vuex'

export default {
  name: 'FormularioImovel',

  data() {
    return {
      nome: '',
      descricao: '',
      preco: '',
      cep: '',
      rua: '',
      bairro: '',
      numero: '',
      cidade: '',
      estado: '',
      complemento: '',
    }

  },

  methods: {
    searchCep() {
      if (this.cep.length == 8) {
        axios.get(`https://viacep.com.br/ws/${this.cep}/json/`)
          .then(response => {
            this.rua = response.data['logradouro'];
            this.bairro = response.data['bairro'];
            this.estado = response.data['uf'];
            this.cidade = response.data['localidade'];
            console.log(this.displayNomeImovel)
          })
          .catch(error => console.log(error))
      }
    },

    async onComplete() {

      let imovel = {
        nome: this.nome,
        descricao: this.descricao,
        preco: this.preco,
        cep: this.cep,
        rua: this.rua,
        bairro: this.bairro,
        numero: this.numero,
        cidade: this.cidade,
        estado: this.estado,
        complemento: this.complemento,
        data_cadastro: this.getNow(),
      }
      await this.$store.dispatch('createImovel', imovel)
        .then(response => {
          console.log(response.data)
        }).catch(error => console.log(error))

      /*
        - fazer layout. header e tal
        - validação dos dados
        - apagar depois do submit
        - fazer mais de uma página
        - dar mais uma lida na ideia pra ver o que precisa fazer

      */


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
      'displayNomeImovel'
    ]),
  },
}
</script>