<template>
    <LoadingSection v-if="isFetching"></LoadingSection>

    <div class="container">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Email</th>
                        <th scope="col">Type</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in users" :value="user.email" :key="user.email">
                        <td>{{ user.data_criacao }}</td>
                        <td> {{ user.email }}</td>
                        <td> {{ user.type }}</td>
                        <td> <a class="btn" v-if="user.type == 'notadm'" @click="changeRole(user)">Tornar ADM</a> <a
                                class="btn" v-else @click="changeRole(user)">Tirar ADM</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <Pagination :offset="offset" :total="total" :limit="limit" :current="current + 1" @change-page="changePage">
        </Pagination>
    </div>
</template>

<script>
import axios from 'axios';
import Pagination from "../Utils/PaginationOfLists.vue"
import LoadingSection from "../Utils/LoadingSection.vue";


// criar trocar de senha e excluir minha conta.
// depois disso... login persistente, cookies e tal


export default {
    data() {
        return {
            users: [],
            offset: 0,
            limit: 10,
            total: 0,
            current: 0,
        }
    },

    components: { Pagination, LoadingSection },


    methods: {
        async getAllUsers() {
            const headers = {
                "Authorization": "Bearer " + this.$store.state.login.token,
            };
            await axios({ url: 'http://localhost:8000/user/listUsers/' + this.offset + '/' + this.limit, method: 'GET', headers: headers })
                .then(response => {
                    this.users = response.data.resultado;
                    this.total = response.data.totalUsers.totalUsers;
                }).catch(error => {
                    if (error.response.status == 401) {
                        this.$store.commit('isLoggedOff');
                        this.$router.push({ name: 'home' });
                        return;
                    }
                })
        },
        async changeRole(user) {
            const headers = {
                "Authorization": "Bearer " + this.$store.state.login.token,
            };
            let payload = {
                type: user.type,
                email: user.email,
            }
            await axios({ url: 'http://localhost:8000/user/alterRole', data: payload, method: 'PUT', headers: headers })
                .then(response => {
                    user.type = response.data.newType;
                }).catch(error => {
                    if (error.response.status == 401) {
                        this.$store.commit('isLoggedOff');
                        this.$router.push({ name: 'home' });
                        return;
                    }
                })
        },

        async changePage(value) {

            if (this.current == value) {
                return;
            }
            else if (this.current > value) {
                this.offset = this.offset - (this.limit * (this.current - value));
            }
            else if (this.current < value) {
                this.offset = this.offset + (this.limit * (value - this.current));
            }
            this.current = value;
            this.$store.commit('isFetching', { status: true, message: 'Carregando...' });

            await this.getAllUsers();

            this.$store.commit('isFetching', { status: false, message: '' });
        },
    },

    async created() {
        await this.getAllUsers();
    }


}
</script>