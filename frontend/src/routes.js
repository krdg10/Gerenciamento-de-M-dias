import * as VueRouter from 'vue-router';
import FormularioImovel from './components/Forms/CriarEditarImovel.vue'
import Home from './components/Dashboards/HomePage.vue'
import DeuRuim from './components/Utils/DeuRuim.vue'
import ListaImoveis from './components/Lists/ListaImoveis.vue'
import ListaArquivos from './components/Lists/ListaArquivos.vue'
import FormularioArquivo from './components/Forms/UploadArquivos.vue'
import { store } from './store/store';

//const Dashboard = () => import('./components/Dashboards/Dashboard.vue');


const routes = [
  { path: '', name: 'home', component: Home },
  { path: '/novoImovel', name: 'novoImovel', component: FormularioImovel, props: true, meta: { requiresAuth: true } },
  { path: '/listaImoveis', name: 'listaImoveis', component: ListaImoveis, meta: { requiresAuth: true } },
  { path: '/listaArquivos', name: 'listaArquivos', component: ListaArquivos, props: true, meta: { requiresAuth: true } },
  { path: '/novoArquivo', name: 'novoArquivo', component: FormularioArquivo, meta: { requiresAuthAdm: true } },
  { path: '/:pathMatch(.*)*', component: DeuRuim }

];

const router = VueRouter.createRouter({
  history: VueRouter.createWebHashHistory(),
  routes: routes,
})

router.beforeEach((to, from, next) => {
  if (to.matched.some(record => record.meta.requiresAuth)) {
    if (store.state.login.isLoggedIn) {
      next()
      return
    }
    next('/')
  }
  else if (to.matched.some(record => record.meta.requiresAuthAdm)) {
    if (store.state.login.isLoggedIn && store.state.login.type == 'adm') {
      next()
      return
    }
    next('/')
  }
  else {
    next()
  }
})

/*router.beforeEach((to, from, next) => {
  if(to.matched.some(record => record.meta.requiresAuth)){
    if(store.getters.isLoggedIn){
      next()
      return
    }
    next('/')
  }
  else if(to.matched.some(record => record.meta.requiresNotAuth)){
    if(store.getters.isLoggedIn){
      next('/dashboard')
      return
    }
    next()
  }
  else if(to.matched.some(record => record.meta.requiresAuthJuridica)){
    if(store.getters.isLoggedIn && store.getters.typeUser==='JURIDICA'){
      next()
      return
    }
    if(store.getters.isLoggedIn){
      next('/dashboard')
      return
    }
    next('/')
  }
  else if(to.matched.some(record => record.meta.requiresAuthFisica)){
    if(store.getters.isLoggedIn && store.getters.typeUser==='FISICA'){
      next()
      return
    }
    if(store.getters.isLoggedIn){
      next('/dashboard')
      return
    }
    next('/')
  }
  else{
    next()
  }
})*/



export default router

