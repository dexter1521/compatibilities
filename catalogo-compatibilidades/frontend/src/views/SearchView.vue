<template>
  <section>
    <h2>Buscador</h2>
    <p class="muted">Consulta de compatibilidades por término.</p>

    <form class="search-form" @submit.prevent="runSearch">
      <input v-model.trim="q" placeholder="Ej. balata ft150" minlength="2" required />
      <button :disabled="loading">{{ loading ? 'Buscando...' : 'Buscar' }}</button>
    </form>

    <p v-if="error" class="error">{{ error }}</p>

    <div v-if="results.length" class="results">
      <article v-for="(item, idx) in results" :key="idx" class="card">
        <h3>{{ item.producto?.nombre || item.nombre || 'Resultado' }}</h3>
        <p class="muted">{{ item.producto?.clave_proveedor || item.clave_proveedor || '-' }}</p>

        <ul v-if="Array.isArray(item.compatibilidades)">
          <li v-for="c in item.compatibilidades" :key="c.id">
            {{ c.marca_nombre || 'Marca' }} {{ c.motocicleta_modelo || c.modelo || '' }}
            <strong>({{ c.score_relevancia ?? 0 }})</strong>
          </li>
        </ul>
      </article>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue'
import { api } from '../services/api'

const q = ref('')
const loading = ref(false)
const error = ref('')
const results = ref([])

const runSearch = async () => {
  loading.value = true
  error.value = ''
  try {
    const resp = await api.get('/search', { params: { q: q.value, limit: 20 } })
    results.value = resp.data?.data?.items || []
  } catch (e) {
    error.value = e?.response?.data?.message || 'No se pudo consultar el buscador.'
    results.value = []
  } finally {
    loading.value = false
  }
}
</script>
