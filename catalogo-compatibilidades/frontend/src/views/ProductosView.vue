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

    <div class="list-toolbar">
      <form class="filters-form" @submit.prevent="onFilterSubmit">
        <input v-model.trim="filters.q" placeholder="Buscar por clave o nombre" />
        <select v-model="filters.sort_by">
          <option value="id">Ordenar: ID</option>
          <option value="nombre">Ordenar: Nombre</option>
          <option value="clave_proveedor">Ordenar: Clave</option>
          <option value="created_at">Ordenar: Creación</option>
          <option value="updated_at">Ordenar: Actualización</option>
        </select>
        <select v-model="filters.sort_dir">
          <option value="asc">Ascendente</option>
          <option value="desc">Descendente</option>
        </select>
        <select v-model="filters.activo">
          <option value="">Estado</option>
          <option value="1">Activo</option>
          <option value="0">Inactivo</option>
        </select>
        <select v-model="filters.enrich_estado">
          <option value="">Enrichment</option>
          <option value="ok">ok</option>
          <option value="sin_tipo">sin_tipo</option>
          <option value="sin_moto">sin_moto</option>
          <option value="sin_ambos">sin_ambos</option>
        </select>
        <select v-model.number="filters.per_page">
          <option :value="10">10</option>
          <option :value="20">20</option>
          <option :value="50">50</option>
          <option :value="100">100</option>
        </select>
        <button>Aplicar</button>
        <button type="button" class="ghost" @click="clearFilters">Limpiar</button>
      </form>
      <p class="muted" v-if="meta.total !== null">Página {{ meta.page }} de {{ meta.last_page }} · {{ meta.total }} registros</p>
    </div>

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
            <td>
              <template v-if="editingId !== row.id">{{ row.clave_proveedor }}</template>
              <input
                v-else
                v-model="editForm.clave_proveedor"
                class="inline-cell"
                placeholder="Clave proveedor"
              />
            </td>
            <td>
              <template v-if="editingId !== row.id">{{ row.nombre }}</template>
              <input v-else v-model="editForm.nombre" class="inline-cell" placeholder="Nombre" />
            </td>
            <td>{{ row.proveedor_nombre || row.proveedor_id }}</td>
            <td>
              <template v-if="editingId !== row.id">{{ row.activo ? 'Activo' : 'Inactivo' }}</template>
              <select v-else v-model="editForm.activo" class="inline-cell">
                <option :value="1">Activo</option>
                <option :value="0">Inactivo</option>
              </select>
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

    <div class="list-pager">
      <button :disabled="loading || meta.page <= 1" @click="prevPage">Anterior</button>
      <button :disabled="loading || meta.page >= meta.last_page" @click="nextPage">Siguiente</button>
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
const meta = ref({
  page: 1,
  per_page: 20,
  total: 0,
  last_page: 1,
  sort_by: 'id',
  sort_dir: 'desc',
  filters: null
})

const filters = ref({
  q: '',
  sort_by: 'id',
  sort_dir: 'desc',
  per_page: 20,
  activo: '',
  enrich_estado: ''
})

const form = ref({ proveedor_id: '', clave_proveedor: '', nombre: '' })
const editingId = ref(null)
const savingId = ref(null)
const editForm = ref({
  clave_proveedor: '',
  nombre: '',
  activo: 1,
  proveedor_id: ''
})

const load = async () => {
  loading.value = true
  error.value = ''
  try {
    const params = {
      page: meta.value.page,
      per_page: filters.value.per_page,
      sort_by: filters.value.sort_by,
      sort_dir: filters.value.sort_dir,
      q: filters.value.q || undefined,
      activo: filters.value.activo === '' ? undefined : Number(filters.value.activo),
      enrich_estado: filters.value.enrich_estado || undefined
    }

    const resp = await api.get('/productos', { params })
    rows.value = resp.data?.data?.items || []
    const rMeta = resp.data?.data?.meta || {}
    meta.value = {
      page: rMeta.page || 1,
      per_page: rMeta.per_page || filters.value.per_page,
      total: rMeta.total || 0,
      last_page: rMeta.last_page || 1,
      sort_by: rMeta.sort_by || filters.value.sort_by,
      sort_dir: rMeta.sort_dir || filters.value.sort_dir,
      filters: rMeta.filters || null
    }
  } catch (e) {
    error.value = e?.response?.data?.message || 'No se pudieron cargar productos.'
  } finally {
    loading.value = false
  }
}

const onFilterSubmit = async () => {
  meta.value.page = 1
  await load()
}

const clearFilters = async () => {
  filters.value = {
    q: '',
    sort_by: 'id',
    sort_dir: 'desc',
    per_page: 20,
    activo: '',
    enrich_estado: ''
  }
  meta.value.page = 1
  await load()
}

const prevPage = async () => {
  if (meta.value.page <= 1 || loading.value) return
  meta.value.page -= 1
  await load()
}

const nextPage = async () => {
  if (meta.value.page >= meta.value.last_page || loading.value) return
  meta.value.page += 1
  await load()
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

const startEdit = (row) => {
  editingId.value = row.id
  editForm.value = {
    clave_proveedor: row.clave_proveedor || '',
    nombre: row.nombre || '',
    proveedor_id: String(row.proveedor_id || ''),
    activo: row.activo ? 1 : 0
  }
}

const cancelEdit = () => {
  editingId.value = null
}

const saveEdit = async (row) => {
  savingId.value = row.id
  error.value = ''
  try {
    await api.put(`/productos/${row.id}`, {
      clave_proveedor: editForm.value.clave_proveedor,
      nombre: editForm.value.nombre,
      proveedor_id: Number(editForm.value.proveedor_id || row.proveedor_id || 0),
      activo: Number(editForm.value.activo),
      enrich_estado: row.enrich_estado || null
    })
    editingId.value = null
    await load()
  } catch (e) {
    error.value = e?.response?.data?.message || 'No se pudo actualizar producto.'
  } finally {
    savingId.value = null
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
