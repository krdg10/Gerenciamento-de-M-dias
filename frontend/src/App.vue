<template>

  <div>
    <NavBar></NavBar>
    <div class="container-fluid">
          <router-view></router-view>

    </div>
  </div>

</template>

<script>
import NavBar from './components/Utils/NavBar.vue'
export default {
  name: 'App',
  components: {  NavBar,  }
}
</script>

