<template>
  <section>
    <div class="section-head">
      <h2>Piezas</h2>
      <button @click="load" :disabled="loading">Recargar</button>
    </div>

    <form class="inline-form" @submit.prevent="createPieza" v-if="isAdmin">
      <input v-model="nombre" placeholder="Nombre de pieza" required />
      <button :disabled="saving">{{ saving ? 'Guardando...' : 'Crear' }}</button>
    </form>

    <p v-if="error" class="error">{{ error }}</p>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Slug</th>
            <th v-if="isAdmin">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in rows" :key="row.id">
            <td>{{ row.id }}</td>
            <td>{{ row.nombre }}</td>
            <td>{{ row.slug }}</td>
            <td v-if="isAdmin"><button class="danger" @click="remove(row.id)">Eliminar</button></td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { api } from '../services/api'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const isAdmin = computed(() => auth.role === 'admin')

const loading = ref(false)
const saving = ref(false)
const error = ref('')
const rows = ref([])
const nombre = ref('')

const load = async () => {
  loading.value = true
  error.value = ''
  try {
    const resp = await api.get('/piezas')
    rows.value = resp.data?.data?.items || []
  } catch (e) {
    error.value = e?.response?.data?.message || 'No se pudieron cargar piezas.'
  } finally {
    loading.value = false
  }
}

const createPieza = async () => {
  saving.value = true
  error.value = ''
  try {
    await api.post('/piezas', { nombre: nombre.value })
    nombre.value = ''
    await load()
  } catch (e) {
    error.value = e?.response?.data?.message || 'No se pudo crear pieza.'
  } finally {
    saving.value = false
  }
}

const remove = async (id) => {
  if (!confirm(`¿Eliminar pieza #${id}?`)) return
  try {
    await api.delete(`/piezas/${id}`)
    await load()
  } catch (e) {
    error.value = e?.response?.data?.message || 'No se pudo eliminar pieza.'
  }
}

onMounted(load)
</script>
