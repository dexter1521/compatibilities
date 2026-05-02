<template>
  <section>
    <div class="section-head">
      <h2>Importador</h2>
    </div>

    <form class="upload-form" @submit.prevent="uploadFile" v-if="isAdmin">
      <input type="file" @change="onFile" accept=".xlsx,.xls,.csv" required />
      <button :disabled="uploading || !file">{{ uploading ? 'Subiendo...' : 'Importar' }}</button>
    </form>

    <p class="muted">Este módulo usa <code>POST /api/v1/import/productos</code>.</p>
    <p v-if="message" class="success">{{ message }}</p>
    <p v-if="error" class="error">{{ error }}</p>
  </section>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const isAdmin = computed(() => auth.role === 'admin')

const file = ref(null)
const uploading = ref(false)
const message = ref('')
const error = ref('')

const onFile = (e) => {
  file.value = e.target.files?.[0] || null
}

const uploadFile = async () => {
  if (!file.value) return

  uploading.value = true
  message.value = ''
  error.value = ''

  try {
    const fd = new FormData()
    fd.append('archivo', file.value)

    const token = localStorage.getItem('sm_access_token')
    const resp = await fetch(`${import.meta.env.VITE_API_BASE_URL || 'http://localhost:8080/api/v1'}/import/productos`, {
      method: 'POST',
      headers: token ? { Authorization: `Bearer ${token}` } : {},
      body: fd
    })

    const json = await resp.json()
    if (!resp.ok) {
      throw new Error(json?.message || 'Error de importación.')
    }

    message.value = `Importación lanzada correctamente. Job: ${json?.data?.job_id ?? 'N/A'}`
    file.value = null
  } catch (e) {
    error.value = e.message || 'No se pudo importar.'
  } finally {
    uploading.value = false
  }
}
</script>
