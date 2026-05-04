<template>
  <section>
    <div class="section-head">
      <h2>Marcas</h2>
      <div class="section-actions">
        <button @click="load" :disabled="loading">Recargar</button>
        <button v-if="isAdmin" class="success" @click="openCreate">Nueva marca</button>
      </div>
    </div>

    <p v-if="error" class="error">{{ error }}</p>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Slug</th>
            <th>Activa</th>
            <th v-if="isAdmin">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in rows" :key="row.id">
            <td>{{ row.id }}</td>
            <td>{{ row.nombre }}</td>
            <td>{{ row.slug }}</td>
            <td>{{ row.activo ? 'Sí' : 'No' }}</td>
            <td v-if="isAdmin">
              <div class="actions">
                <div class="row-menu">
                  <button
                    class="icon-btn"
                    @click="toggleMenu(row.id)"
                    type="button"
                    :aria-label="`Acciones de ${row.nombre}`"
                  >
                    ⋮
                  </button>
                  <div v-if="menuOpenForId === row.id" class="dropdown">
                    <button type="button" @click="openEdit(row)">Editar</button>
                    <button type="button" class="danger" @click="remove(row.id)">Eliminar</button>
                  </div>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="showModal" class="modal-backdrop" @click.self="closeModal">
      <div class="modal">
        <h3>{{ editingId ? 'Editar marca' : 'Nueva marca' }}</h3>
        <form class="inline-form" @submit.prevent="saveMarca">
          <input v-model="form.nombre" placeholder="Nombre de marca" required />
          <select v-model="form.activo">
            <option :value="1">Sí</option>
            <option :value="0">No</option>
          </select>
          <div class="modal-actions">
            <button type="button" class="ghost" @click="closeModal">Cancelar</button>
            <button :disabled="saving">{{ saving ? 'Guardando...' : 'Guardar' }}</button>
          </div>
        </form>
      </div>
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
const showModal = ref(false)
const editingId = ref(null)
const menuOpenForId = ref(null)
const form = ref({
  nombre: '',
  activo: 1
})

const load = async () => {
  loading.value = true
  error.value = ''
  try {
    const resp = await api.get('/marcas')
    rows.value = resp.data?.data?.items || []
  } catch (e) {
    error.value = e?.response?.data?.message || 'No se pudieron cargar las marcas.'
  } finally {
    loading.value = false
  }
}

const openCreate = () => {
  editingId.value = null
  form.value = {
    nombre: '',
    activo: 1
  }
  showModal.value = true
  closeMenu()
}

const openEdit = (row) => {
  editingId.value = row.id
  form.value = {
    nombre: row.nombre || '',
    activo: row.activo ? 1 : 0
  }
  showModal.value = true
  closeMenu()
}

const closeModal = () => {
  showModal.value = false
}

const saveMarca = async () => {
  saving.value = true
  error.value = ''
  try {
    if (editingId.value) {
      await api.put(`/marcas/${editingId.value}`, {
        nombre: form.value.nombre,
        activo: Number(form.value.activo)
      })
    } else {
      await api.post('/marcas', {
        nombre: form.value.nombre,
        activo: Number(form.value.activo)
      })
    }

    closeModal()
    await load()
  } catch (e) {
    error.value = e?.response?.data?.message || (editingId.value
      ? 'No se pudo actualizar la marca.'
      : 'No se pudo crear la marca.')
  } finally {
    saving.value = false
  }
}

const remove = async (id) => {
  closeMenu()
  if (!confirm(`¿Eliminar marca #${id}?`)) return
  try {
    await api.delete(`/marcas/${id}`)
    await load()
  } catch (e) {
    error.value = e?.response?.data?.message || 'No se pudo eliminar la marca.'
  }
}

const toggleMenu = (id) => {
  menuOpenForId.value = menuOpenForId.value === id ? null : id
}

const closeMenu = () => {
  menuOpenForId.value = null
}

onMounted(load)
</script>

<style scoped>
.section-actions {
  display: flex;
  gap: 10px;
}

.success {
  background: #1a7f37;
}

.success:hover {
  background: #166a2d;
}

.actions {
  justify-content: flex-end;
}

.row-menu {
  position: relative;
}

.icon-btn {
  width: 36px;
  height: 36px;
  border-radius: 8px;
}

.icon-btn:hover {
  background: #f3f4f6;
}

.dropdown {
  position: absolute;
  right: 0;
  top: 100%;
  margin-top: 6px;
  display: grid;
  min-width: 120px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  overflow: hidden;
  background: #fff;
  z-index: 20;
}

.dropdown button {
  text-align: left;
  width: 100%;
  border: 0;
  border-bottom: 1px solid #eef2ff;
  border-radius: 0;
  padding: 10px 12px;
  background: #fff;
}

.dropdown button:last-child {
  border-bottom: 0;
}

.dropdown button:hover {
  background: #f9fafb;
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgb(15 23 42 / 55%);
  display: grid;
  place-items: center;
  z-index: 30;
  padding: 16px;
}

.modal {
  width: min(460px, 100%);
  background: #fff;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
  padding: 16px;
  display: grid;
  gap: 10px;
}

.modal h3 {
  margin: 0;
}

.modal .inline-form {
  margin: 0;
}

.modal .inline-form > * {
  min-width: 0;
}

.modal-actions {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
  margin-top: 8px;
}

.modal-actions button:first-child {
  max-width: 120px;
}

.modal-actions .ghost {
  min-width: 110px;
}

@media (max-width: 900px) {
  .section-head {
    align-items: flex-start;
  }

  .section-actions {
    flex-wrap: wrap;
  }
}
</style>
