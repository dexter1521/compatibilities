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
            <td>
              <template v-if="editingId !== row.id">{{ row.nombre }}</template>
              <input v-else v-model="editForm.nombre" class="inline-cell" placeholder="Nombre de pieza" />
            </td>
            <td>{{ row.slug }}</td>
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
const nombre = ref('')
const editingId = ref(null)
const savingId = ref(null)
const editForm = ref({ nombre: '' })

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

const startEdit = (row) => {
  editingId.value = row.id
  editForm.value = { nombre: row.nombre || '' }
}

const cancelEdit = () => {
  editingId.value = null
}

const saveEdit = async (row) => {
  savingId.value = row.id
  error.value = ''
  try {
    await api.put(`/piezas/${row.id}`, {
      nombre: editForm.value.nombre
    })
    editingId.value = null
    await load()
  } catch (e) {
    error.value = e?.response?.data?.message || 'No se pudo actualizar la pieza.'
  } finally {
    savingId.value = null
  }
}

onMounted(load)
</script>
