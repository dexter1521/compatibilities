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
            <td>
              <template v-if="editingId !== row.id">{{ row.marca_id }}</template>
              <input v-else v-model.number="editForm.marca_id" type="number" min="1" class="inline-cell" />
            </td>
            <td>
              <template v-if="editingId !== row.id">{{ row.modelo }}</template>
              <input v-else v-model="editForm.modelo" class="inline-cell" placeholder="Modelo" />
            </td>
            <td>
              <template v-if="editingId !== row.id">{{ row.anio_desde || '-' }} - {{ row.anio_hasta || '-' }}</template>
              <div v-else class="actions">
                <input v-model.number="editForm.anio_desde" type="number" min="1900" max="2100" class="inline-cell" placeholder="Año desde" />
                <input v-model.number="editForm.anio_hasta" type="number" min="1900" max="2100" class="inline-cell" placeholder="Año hasta" />
              </div>
            </td>
            <td v-if="isAdmin">
              <div class="actions" v-if="editingId !== row.id">
                <button @click="startEdit(row)">Editar</button>
                <button class="danger" @click="remove(row.id)">Eliminar</button>
              </div>
              <div class="actions" v-else>
                <button :disabled="saving && savingId === row.id" @click="saveEdit(row)">
                  {{ saving && savingId === row.id ? 'Guardando...' : 'Guardar' }}
                </button>
                <button class="ghost" type="button" @click="cancelEdit">Cancelar</button>
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
const editingId = ref(null)
const savingId = ref(null)
const editForm = ref({
  marca_id: '',
  modelo: '',
  anio_desde: '',
  anio_hasta: '',
  cilindrada: ''
})

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

const startEdit = (row) => {
  editingId.value = row.id
  editForm.value = {
    marca_id: String(row.marca_id || ''),
    modelo: row.modelo || '',
    anio_desde: row.anio_desde || '',
    anio_hasta: row.anio_hasta || '',
    cilindrada: row.cilindrada || ''
  }
}

const cancelEdit = () => {
  editingId.value = null
}

const saveEdit = async (row) => {
  savingId.value = row.id
  error.value = ''
  try {
    await api.put(`/motocicletas/${row.id}`, {
      marca_id: Number(editForm.value.marca_id || row.marca_id || 0),
      modelo: editForm.value.modelo,
      anio_desde: editForm.value.anio_desde === '' ? null : Number(editForm.value.anio_desde),
      anio_hasta: editForm.value.anio_hasta === '' ? null : Number(editForm.value.anio_hasta),
      cilindrada: editForm.value.cilindrada || null
    })
    editingId.value = null
    await load()
  } catch (e) {
    error.value = e?.response?.data?.message || 'No se pudo actualizar motocicleta.'
  } finally {
    savingId.value = null
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
