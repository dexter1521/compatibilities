<template>
  <div class="app-shell">
    <aside :class="['sidebar', { open: menuOpen }]">
      <div class="brand">
        <span>SM</span>
        <small>Compatibilidades</small>
      </div>
      <nav>
        <RouterLink to="/">Buscador</RouterLink>
        <RouterLink to="/buscador">Búsqueda</RouterLink>
      </nav>
    </aside>

    <div class="main">
      <header class="topbar">
        <button class="menu-btn" @click="menuOpen = !menuOpen">☰</button>
        <div class="topbar-right">
          <span>{{ auth.user?.email }}</span>
          <button @click="logout">Salir</button>
        </div>
      </header>

      <main class="content">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const router = useRouter()
const menuOpen = ref(false)

const logout = async () => {
  await auth.logout()
  router.push({ name: 'login' })
}
</script>
