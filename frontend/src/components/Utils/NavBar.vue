<template>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3 navbar-shrink" id="mainNav">

        <div class="navbar-brand d-flex align-items-center border-end px-4 px-lg-5">
            <h5 class="m-0 text-primary">
                <router-link to="/" id="iconNav">Home</router-link>
            </h5>
        </div>
        <button type="button" class="navbar-toggler me-4" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse" v-if="this.$store.state.login.isLoggedIn">
            <div class="navbar-nav ms-auto p-4 p-lg-0">
                <div class="nav-item dropdown" v-if="this.$store.state.login.type == 'master'">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarUsers" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        Usuários </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarUsers">
                        <router-link class="nav-item nav-link text-center toToggleCollapse" to="/listaUsers">Lista de
                            Usuários
                        </router-link>

                    </ul>
                </div>
                <div class="nav-item dropdown"
                    v-if="this.$store.state.login.type == 'adm' || this.$store.state.login.type == 'master'">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarNovasMidias" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        Novas Mídias </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarNovasMidias">
                        <router-link class="nav-item nav-link text-center toToggleCollapse" to="/novoImovel">Novo Imovel
                        </router-link>
                        <router-link class="nav-item nav-link text-center toToggleCollapse" to="/novoArquivo">Novo
                            Arquivo</router-link>
                    </ul>
                </div>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarMinhasMidias" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        Mídias </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarMinhasMidias">
                        <router-link class="nav-item nav-link text-center toToggleCollapse" to="/listaImoveis">Imoveis
                        </router-link>
                        <router-link class="nav-item nav-link text-center toToggleCollapse" to="/listaArquivos">Arquivos
                        </router-link>
                    </ul>
                </div>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarMinhaConta" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        Minha Conta </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarMinhasMidias">
                        <router-link class="nav-item nav-link text-center toToggleCollapse" id="navbarMinhaConta"
                            to="/editarPerfil">Editar
                        </router-link>
                        <a class="nav-item nav-link text-center toToggleCollapse" @click="logout()">Logout
                        </a>
                    </ul>
                </div>

            </div>
        </div>
    </nav>

</template>

<script>

export default {
    methods: {
        logout() {
            this.$store.commit('isLoggedOff');
            this.$router.push({ name: 'home' });
        }
    }
}

</script>