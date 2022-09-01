<template>
    <div class="row">
        <div class="col-lg-6">
            <div class="mb-3">
                <label for="nome" class="form-label">Nome:</label>
                <Field type="text" class="form-control" id="nome" placeholder="Nome do Imóvel" name="nome"
                    v-model="nome" required />
                <ErrorMessage name="nome" />
            </div>
            <div class="mb-3">
                <label for="preco" class="form-label">Preço:</label>
                <Field type="number" class="form-control" id="preco" placeholder="Preço do Imóvel" name="preco"
                    v-model="preco" required />
                <ErrorMessage name="preco" />
            </div>
            <div class="mb-3">
                <label for="descricao" class="form-label">Descrição:</label>
                <Field as="textarea" type="text" class="form-control" id="descricao" placeholder="Descrição do imóvel"
                    name="descricao" v-model="descricao" required />
                <ErrorMessage name="descricao" />
            </div>
        </div>
        <div class="col-lg-6">
            <div class="mb-3">
                <label for="cep" class="form-label">CEP:</label>
                <Field type="text" class="form-control" id="cep" placeholder="CEP do imóvel" name="cep" v-model="cep"
                    @keyup="searchCep()" required />
                <ErrorMessage name="cep" />
            </div>
            <div class="mb-3">
                <label for="rua" class="form-label">Rua:</label>
                <Field type="text" class="form-control" id="rua" placeholder="Rua do imóvel" name="rua" v-model="rua"
                    required />
                <ErrorMessage name="rua" />
            </div>
            <div class="mb-3">
                <label for="bairro" class="form-label">Bairro:</label>
                <Field type="text" class="form-control" id="bairro" placeholder="Bairro do imóvel" name="bairro"
                    v-model="bairro" required />
                <ErrorMessage name="bairro" />
            </div>
            <div class="mb-3">
                <label for="numero" class="form-label">Número:</label>
                <Field type="text" class="form-control" id="numero" placeholder="Número do imóvel" name="numero"
                    v-model="numero" required />
                <ErrorMessage name="numero" />
            </div>
            <div class="mb-3">
                <label for="cidade" class="form-label">Cidade:</label>
                <Field type="text" class="form-control" id="cidade" placeholder="Cidade do imóvel" name="cidade"
                    v-model="cidade" required />
                <ErrorMessage name="cidade" />

            </div>
            <div class="mb-3">
                <label for="estado" class="form-label">Estado:</label>
                <Field as="select" class="form-control" id="estado" placeholder="Estado do imóvel" name="estado"
                    v-model="estado">
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
                </Field>
                <ErrorMessage name="estado" />

            </div>
            <div class="mb-3">
                <label for="complemento" class="form-label">Complemento:</label>
                <Field type="text" class="form-control" id="complemento" placeholder="Complemento do imóvel"
                    name="complemento" v-model="complemento" required />
                <ErrorMessage name="complemento" />

            </div>
        </div>

    </div>
</template>

<script>
import axios from 'axios'

import { Field, ErrorMessage } from 'vee-validate';
import '../../assets/validations';

export default {

    name: 'FormulariodeImovel',

    props: {
        imovel: Object
    },

    data() {
        if (this.imovel) {
            return {
                nome: this.imovel.nome,
                descricao: this.imovel.descricao,
                preco: this.imovel.preco,
                cep: this.imovel.cep,
                rua: this.imovel.rua,
                bairro: this.imovel.bairro,
                numero: this.imovel.numero,
                cidade: this.imovel.cidade,
                estado: this.imovel.estado,
                complemento: this.imovel.complemento
            }
        }
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
    components: { Field, ErrorMessage },

    methods: {
        searchCep() {
            if (this.cep.length == 8) {
                axios.get(`https://viacep.com.br/ws/${this.cep}/json/`)
                    .then(response => {
                        this.rua = response.data['logradouro'];
                        this.bairro = response.data['bairro'];
                        this.estado = response.data['uf'];
                        this.cidade = response.data['localidade'];
                        //console.log(this.displayNomeImovel)
                    })
                    .catch(error => console.log(error))
            }
        }
    }
}
</script>