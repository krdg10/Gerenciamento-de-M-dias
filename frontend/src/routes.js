import * as VueRouter from 'vue-router';
import FormularioImovel from './components/Forms/CriarEditarImovel.vue'
import Home from './components/Dashboards/HomePage.vue'
import DeuRuim from './components/Utils/DeuRuim.vue'
import ListaImoveis from './components/Lists/ListaImoveis.vue'
import ListaArquivos from './components/Lists/ListaArquivos.vue'
import FormularioArquivo from './components/Forms/UploadArquivos.vue'
import ListaUsers from './components/Lists/ListaUsers.vue'
import EditarPerfil from './components/Forms/TrocarSenha.vue'
import { store } from './store/store';
import axios from 'axios';
import VueCookies from 'vue-cookies'


const routes = [
  { path: '', name: 'home', component: Home },
  { path: '/novoImovel', name: 'novoImovel', component: FormularioImovel, props: true, meta: { requiresAuth: true } },
  { path: '/listaImoveis', name: 'listaImoveis', component: ListaImoveis, meta: { requiresAuth: true } },
  { path: '/listaArquivos', name: 'listaArquivos', component: ListaArquivos, props: true, meta: { requiresAuth: true } },
  { path: '/novoArquivo', name: 'novoArquivo', component: FormularioArquivo, meta: { requiresAuthAdm: true } },
  { path: '/listaUsers', name: 'listaUsers', component: ListaUsers, meta: { requiresAuthMaster: true } },
  { path: '/editarPerfil', name: 'editarPerfil', component: EditarPerfil, meta: { requiresAuth: true } },
  { path: '/:pathMatch(.*)*', component: DeuRuim }
];

const router = VueRouter.createRouter({
  history: VueRouter.createWebHashHistory(),
  routes: routes,
})

router.beforeEach((to, from, next) => {
  verifyToken();
  if (to.matched.some(record => record.meta.requiresAuth)) {
    if (store.state.login.isLoggedIn) {
      next()
      return
    }
    next('/naoexiste')
  }
  else if (to.matched.some(record => record.meta.requiresAuthAdm)) {
    if (store.state.login.isLoggedIn && (store.state.login.type == 'adm' || store.state.login.type == 'master')) {
      next()
      return
    }
    next('/naoexiste')
  }
  else if (to.matched.some(record => record.meta.requiresAuthMaster)) {
    if (store.state.login.isLoggedIn && store.state.login.type == 'master') {
      next()
      return
    }
    next('/naoexiste')
  }
  else {
    next()
  }
})

async function verifyToken() {

  const headers = {
    "Authorization": "Bearer " + VueCookies.get('token'),
  };

  await axios({ url: 'http://localhost:8000/user/verifyToken', headers: headers, method: 'GET' })
    .then(response => {
      VueCookies.set('isLoggedIn', 'true')
      VueCookies.set('type', response.data.type)
      store.state.login.isLoggedIn = true;
      store.state.login.type = response.data.type;
      store.state.login.token = VueCookies.get('token');
    }).catch(error => {
      console.log(error)
      VueCookies.set('isLoggedIn', 'false')
      VueCookies.set('type', 'false')
      VueCookies.set('token', 'false')
    })

}


export default router

