import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import LoginView from '../views/LoginView.vue'
import DashboardView from '../views/DashboardView.vue'
import SearchView from '../views/SearchView.vue'
import ProductosView from '../views/ProductosView.vue'
import MotocicletasView from '../views/MotocicletasView.vue'
import PiezasView from '../views/PiezasView.vue'
import CompatibilidadesView from '../views/CompatibilidadesView.vue'
import ImportView from '../views/ImportView.vue'
import AliasesView from '../views/AliasesView.vue'

const router = createRouter({
  history: createWebHistory('/app/'),
  routes: [
    { path: '/login', name: 'login', component: LoginView, meta: { guest: true } },
    {
      path: '/',
      component: DashboardView,
      meta: { requiresAuth: true },
      children: [
        { path: '', name: 'home', component: SearchView },
        { path: 'buscador', name: 'buscador', component: SearchView },
        { path: 'productos', name: 'productos', component: ProductosView },
        { path: 'motocicletas', name: 'motocicletas', component: MotocicletasView },
        { path: 'piezas', name: 'piezas', component: PiezasView },
        { path: 'compatibilidades', name: 'compatibilidades', component: CompatibilidadesView },
        { path: 'aliases', name: 'aliases', component: AliasesView },
        { path: 'import', name: 'import', component: ImportView }
      ]
    }
  ]
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()

  if (!auth.initialized) {
    await auth.bootstrap()
  }

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: 'login' }
  }

  if (to.meta.guest && auth.isAuthenticated) {
    return { name: 'home' }
  }

  return true
})

export default router
