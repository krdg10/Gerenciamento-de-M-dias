import * as VueRouter from 'vue-router';
import FormularioImovel from './components/Forms/CriarEditarImovel.vue'
import MyDashboard from './components/Dashboards/MyDashboard.vue'
import DeuRuim from './components/Utils/DeuRuim.vue'
import ListaImoveis from './components/Lists/ListaImoveis.vue'
import ListaArquivos from './components/Lists/ListaArquivos.vue'
import FormularioArquivo from './components/Forms/UploadArquivos.vue'

//const Dashboard = () => import('./components/Dashboards/Dashboard.vue');


const routes = [
  { path: '', name: 'dash', component: MyDashboard },
  { path: '/novoImovel', name: 'novoImovel', component: FormularioImovel, props: true },
  { path: '/listaImoveis', name: 'listaImoveis', component: ListaImoveis },
  { path: '/listaArquivos', name: 'listaArquivos', component: ListaArquivos },
  { path: '/novoArquivo', name: 'novoArquivo', component: FormularioArquivo },
  { path: '/:pathMatch(.*)*', component: DeuRuim }

];

const router = VueRouter.createRouter({
  history: VueRouter.createWebHashHistory(),
  routes: routes,
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

