<template>
  <section>
    <div class="section-head">
      <h2>Compatibilidades</h2>
      <button @click="load" :disabled="loading">Recargar</button>
    </div>

    <form class="inline-form" @submit.prevent="createItem" v-if="isAdmin">
      <input v-model.number="form.pieza_maestra_id" type="number" min="1" placeholder="Pieza ID" required />
      <input v-model.number="form.motocicleta_id" type="number" min="1" placeholder="Motocicleta ID" required />
      <button :disabled="saving">{{ saving ? 'Guardando...' : 'Crear' }}</button>
    </form>

    <p v-if="error" class="error">{{ error }}</p>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Pieza</th>
            <th>Moto</th>
            <th>Confirmada</th>
            <th>Score</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in rows" :key="row.id">
            <td>{{ row.id }}</td>
            <td>{{ row.pieza_nombre || row.pieza_maestra_id }}</td>
            <td>{{ row.modelo || row.motocicleta_id }}</td>
            <td>{{ row.confirmada ? 'Sí' : 'No' }}</td>
            <td>{{ row.contador_confirmaciones ?? 0 }}</td>
            <td class="actions">
              <button @click="confirmar(row.id)">Confirmar</button>
              <button class="danger" v-if="isAdmin" @click="remove(row.id)">Eliminar</button>
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
const form = ref({ pieza_maestra_id: '', motocicleta_id: '' })

const load = async () => {
  loading.value = true
  error.value = ''
  try {
    const resp = await api.get('/compatibilidades')
    rows.value = resp.data?.data?.items || []
  } catch (e) {
    error.value = e?.response?.data?.message || 'No se pudieron cargar compatibilidades.'
  } finally {
    loading.value = false
  }
}

const createItem = async () => {
  saving.value = true
  error.value = ''
  try {
    await api.post('/compatibilidades', {
      pieza_maestra_id: form.value.pieza_maestra_id,
      motocicleta_id: form.value.motocicleta_id,
      confirmada: 0
    })
    form.value = { pieza_maestra_id: '', motocicleta_id: '' }
    await load()
  } catch (e) {
    error.value = e?.response?.data?.message || 'No se pudo crear compatibilidad.'
  } finally {
    saving.value = false
  }
}

const confirmar = async (id) => {
  try {
    await api.patch(`/compatibilidades/${id}/confirmar`)
    await load()
  } catch (e) {
    error.value = e?.response?.data?.message || 'No se pudo confirmar.'
  }
}

const remove = async (id) => {
  if (!confirm(`¿Eliminar compatibilidad #${id}?`)) return
  try {
    await api.delete(`/compatibilidades/${id}`)
    await load()
  } catch (e) {
    error.value = e?.response?.data?.message || 'No se pudo eliminar compatibilidad.'
  }
}

onMounted(load)
</script>
