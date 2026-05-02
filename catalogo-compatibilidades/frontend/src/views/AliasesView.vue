<template>
  <section>
    <div class="section-head">
      <h2>Aliases</h2>
      <button @click="load" :disabled="loading">Recargar</button>
    </div>

    <form class="inline-form" @submit.prevent="createAlias" v-if="isAdmin">
      <input v-model.number="form.motocicleta_id" type="number" min="1" placeholder="ID de motocicleta" required />
      <input v-model="form.alias" placeholder="Alias" required />
      <button :disabled="saving">{{ saving ? 'Guardando...' : 'Crear' }}</button>
    </form>

    <p v-if="error" class="error">{{ error }}</p>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Alias</th>
            <th>Slug</th>
            <th>Motocicleta</th>
            <th v-if="isAdmin">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in rows" :key="row.id">
            <td>{{ row.id }}</td>
            <td>{{ row.alias }}</td>
            <td>{{ row.slug }}</td>
            <td>{{ row.marca_nombre ? row.marca_nombre + ' - ' : '' }}{{ row.moto_modelo || row.motocicleta_id }}</td>
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
const form = ref({ motocicleta_id: '', alias: '' })

const load = async () => {
  loading.value = true
  error.value = ''
  try {
    const resp = await api.get('/aliases')
    rows.value = resp.data?.data?.items || []
  } catch (e) {
    error.value = e?.response?.data?.message || 'No se pudieron cargar los aliases.'
  } finally {
    loading.value = false
  }
}

const createAlias = async () => {
  saving.value = true
  error.value = ''
  try {
    await api.post('/aliases', {
      motocicleta_id: form.value.motocicleta_id,
      alias: form.value.alias
    })
    form.value = { motocicleta_id: '', alias: '' }
    await load()
  } catch (e) {
    error.value = e?.response?.data?.message || 'No se pudo crear el alias.'
  } finally {
    saving.value = false
  }
}

const remove = async (id) => {
  if (!confirm(`¿Eliminar alias #${id}?`)) return
  try {
    await api.delete(`/aliases/${id}`)
    await load()
  } catch (e) {
    error.value = e?.response?.data?.message || 'No se pudo eliminar el alias.'
  }
}

onMounted(load)
</script>
