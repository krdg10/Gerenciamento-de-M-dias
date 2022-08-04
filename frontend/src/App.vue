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
  components: { NavBar, }
}
</script>

<style lang="scss">
@import url("https://fonts.googleapis.com/css2?family=Karla:wght@300;400&display=swap");

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: "Karla", sans-serif;
}

button {
  padding: 20px 30px;
  border: none;
  font-size: 16px;
  background-color: crimson;
  color: #fff;
  cursor: pointer;
}
</style>