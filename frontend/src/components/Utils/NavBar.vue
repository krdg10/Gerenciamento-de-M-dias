<template>
    <nav class="navbar navbar-expand-sm bg-dark navbar-dark">
        <div class="container-fluid">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <router-link class="nav-link active" to="/">Home</router-link>
                </li>
                <li class="nav-item">
                    <router-link class="nav-link active" to="/novoImovel">Novo Imovel</router-link>
                </li>
                <li class="nav-item">
                    <router-link class="nav-link active" to="/listaImoveis">Imoveis</router-link>
                </li>
                <li class="nav-item">
                    <a class="nav-link disabled" href="#">Disabled</a>
                </li>
            </ul>
        </div>
    </nav>

</template>

<script>
export default {

}
</script>

