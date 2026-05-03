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
            <td>
              <template v-if="editingId !== row.id">{{ row.alias }}</template>
              <input v-else v-model="editForm.alias" class="inline-cell" placeholder="Alias" />
            </td>
            <td>{{ row.slug }}</td>
            <td>
              <template v-if="editingId !== row.id">{{ row.marca_nombre ? row.marca_nombre + ' - ' : '' }}{{ row.moto_modelo || row.motocicleta_id }}</template>
              <input v-else v-model.number="editForm.motocicleta_id" type="number" min="1" class="inline-cell" />
            </td>
            <td v-if="isAdmin">
              <div class="actions">
                <template v-if="editingId !== row.id">
                  <button @click="startEdit(row)">Editar</button>
                  <button class="danger" @click="remove(row.id)">Eliminar</button>
                </template>
                <template v-else>
                  <button :disabled="saving && savingId === row.id" @click="saveEdit(row)">
                    {{ saving && savingId === row.id ? 'Guardando...' : 'Guardar' }}
                  </button>
                  <button class="ghost" type="button" @click="cancelEdit">Cancelar</button>
                </template>
              </div>
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
const editingId = ref(null)
const savingId = ref(null)
const editForm = ref({ motocicleta_id: '', alias: '' })

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

const startEdit = (row) => {
  editingId.value = row.id
  editForm.value = {
    motocicleta_id: String(row.motocicleta_id || ''),
    alias: row.alias || ''
  }
}

const cancelEdit = () => {
  editingId.value = null
}

const saveEdit = async (row) => {
  savingId.value = row.id
  error.value = ''
  try {
    await api.put(`/aliases/${row.id}`, {
      motocicleta_id: Number(editForm.value.motocicleta_id || row.motocicleta_id || 0),
      alias: editForm.value.alias
    })
    editingId.value = null
    await load()
  } catch (e) {
    error.value = e?.response?.data?.message || 'No se pudo actualizar el alias.'
  } finally {
    savingId.value = null
  }
}

onMounted(load)
</script>
