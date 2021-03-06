import * as VueRouter from 'vue-router';
import FormularioImovel from './components/Forms/FormularioImovel.vue'
import MyDashboard from './components/Utils/MyDashboard.vue'
import DeuRuim from './components/Utils/DeuRuim.vue'

//const Dashboard = () => import('./components/Dashboards/Dashboard.vue');


const routes = [
    { path: '', name: 'dash', component: MyDashboard },
    { path: '/novoImovel', name: 'novoImovel', component: FormularioImovel },

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

