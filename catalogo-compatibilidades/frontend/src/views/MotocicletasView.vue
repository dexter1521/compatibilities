<template>
  <section>
    <div class="section-head">
      <h2>Motocicletas</h2>
      <button @click="load" :disabled="loading">Recargar</button>
    </div>

    <form class="inline-form" @submit.prevent="createMoto" v-if="isAdmin">
      <input v-model.number="form.marca_id" type="number" min="1" placeholder="Marca ID" required />
      <input v-model="form.modelo" placeholder="Modelo" required />
      <button :disabled="saving">{{ saving ? 'Guardando...' : 'Crear' }}</button>
    </form>

    <p v-if="error" class="error">{{ error }}</p>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Marca ID</th>
            <th>Modelo</th>
            <th>Año</th>
            <th v-if="isAdmin">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in rows" :key="row.id">
            <td>{{ row.id }}</td>
            <td>{{ row.marca_id }}</td>
            <td>{{ row.modelo }}</td>
            <td>{{ row.anio_desde || '-' }} - {{ row.anio_hasta || '-' }}</td>
            <td v-if="isAdmin">
              <button class="danger" @click="remove(row.id)">Eliminar</button>
            </td>
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

const form = ref({ marca_id: '', modelo: '' })

const load = async () => {
  loading.value = true
  error.value = ''
  try {
    const resp = await api.get('/motocicletas')
    rows.value = resp.data?.data?.items || []
  } catch (e) {
    error.value = e?.response?.data?.message || 'No se pudieron cargar motocicletas.'
  } finally {
    loading.value = false
  }
}

const createMoto = async () => {
  saving.value = true
  error.value = ''
  try {
    await api.post('/motocicletas', {
      marca_id: form.value.marca_id,
      modelo: form.value.modelo
    })
    form.value = { marca_id: '', modelo: '' }
    await load()
  } catch (e) {
    error.value = e?.response?.data?.message || 'No se pudo crear motocicleta.'
  } finally {
    saving.value = false
  }
}

const remove = async (id) => {
  if (!confirm(`¿Eliminar motocicleta #${id}?`)) return
  try {
    await api.delete(`/motocicletas/${id}`)
    await load()
  } catch (e) {
    error.value = e?.response?.data?.message || 'No se pudo eliminar motocicleta.'
  }
}

onMounted(load)
</script>
