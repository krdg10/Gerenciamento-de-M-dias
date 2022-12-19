<template>
    <MyDashboard v-if="login.isLoggedIn"></MyDashboard>
    <Login v-else></Login>

</template>

<script>
import MyDashboard from "./MyDashboard.vue";
import Login from "../Forms/LoginForm.vue";
import { mapState } from "vuex";


export default {
    components: { MyDashboard, Login },
    computed: {
        ...mapState([
            "login",
        ]),
    },

}
</script>
