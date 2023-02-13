import { createApp } from 'vue'
import App from './App.vue'
import "bootstrap/dist/css/bootstrap.min.css"
import "bootstrap"
import axios from 'axios'
import VueAxios from 'vue-axios'
import { store } from './store/store'
import router from './routes'
import { setLocale } from '@vee-validate/i18n';
import VueCookies from 'vue-cookies'

setLocale('pt_BR');

const app = createApp(App)
app.use(VueAxios, axios)
app.use(store)
app.use(router)
app.use(VueCookies)
app.mount('#app');

