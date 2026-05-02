<template>
  <div class="auth-wrap">
    <div class="auth-card">
      <h1>Shark Motors</h1>
      <p>Catálogo de compatibilidades</p>

      <form @submit.prevent="handleLogin">
        <label>Correo</label>
        <input v-model.trim="email" type="email" required placeholder="admin@sharkmotors.local" />

        <label>Contraseña</label>
        <input v-model="password" type="password" required placeholder="••••••••" />

        <button :disabled="auth.loading" type="submit">
          {{ auth.loading ? 'Entrando...' : 'Iniciar sesión' }}
        </button>

        <p v-if="error" class="error">{{ error }}</p>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const router = useRouter()

const email = ref('admin@sharkmotors.local')
const password = ref('Admin123!')
const error = ref('')

const handleLogin = async () => {
  error.value = ''
  try {
    await auth.login(email.value, password.value)
    router.push({ name: 'home' })
  } catch (e) {
    error.value = e?.response?.data?.message || 'No se pudo iniciar sesión.'
  }
}
</script>
