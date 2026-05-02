<template>
  <section>
    <div class="section-head">
      <h2>Productos</h2>
      <button @click="load" :disabled="loading">Recargar</button>
    </div>

    <form class="inline-form" @submit.prevent="createProducto" v-if="isAdmin">
      <input v-model.number="form.proveedor_id" type="number" min="1" placeholder="Proveedor ID" required />
      <input v-model="form.clave_proveedor" placeholder="Clave proveedor" required />
      <input v-model="form.nombre" placeholder="Nombre" required />
      <button :disabled="saving">{{ saving ? 'Guardando...' : 'Crear' }}</button>
    </form>

    <p v-if="error" class="error">{{ error }}</p>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Clave</th>
            <th>Nombre</th>
            <th>Proveedor</th>
            <th>Estado</th>
            <th v-if="isAdmin">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in rows" :key="row.id">
            <td>{{ row.id }}</td>
            <td>{{ row.clave_proveedor }}</td>
            <td>{{ row.nombre }}</td>
            <td>{{ row.proveedor_nombre || row.proveedor_id }}</td>
            <td>{{ row.activo ? 'Activo' : 'Inactivo' }}</td>
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

const form = ref({ proveedor_id: '', clave_proveedor: '', nombre: '' })

const load = async () => {
  loading.value = true
  error.value = ''
  try {
    const resp = await api.get('/productos', { params: { per_page: 50, page: 1 } })
    rows.value = resp.data?.data?.items || []
  } catch (e) {
    error.value = e?.response?.data?.message || 'No se pudieron cargar productos.'
  } finally {
    loading.value = false
  }
}

const createProducto = async () => {
  saving.value = true
  error.value = ''
  try {
    await api.post('/productos', {
      proveedor_id: form.value.proveedor_id,
      clave_proveedor: form.value.clave_proveedor,
      nombre: form.value.nombre,
      activo: 1
    })
    form.value = { proveedor_id: '', clave_proveedor: '', nombre: '' }
    await load()
  } catch (e) {
    error.value = e?.response?.data?.message || 'No se pudo crear producto.'
  } finally {
    saving.value = false
  }
}

const remove = async (id) => {
  if (!confirm(`¿Eliminar producto #${id}?`)) return
  try {
    await api.delete(`/productos/${id}`)
    await load()
  } catch (e) {
    error.value = e?.response?.data?.message || 'No se pudo eliminar producto.'
  }
}

onMounted(load)
</script>
