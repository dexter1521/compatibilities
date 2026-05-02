import { defineStore } from 'pinia'
import { api } from '../services/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    loading: false,
    initialized: false
  }),

  getters: {
    isAuthenticated: (state) => Boolean(state.user && localStorage.getItem('sm_access_token')),
    role: (state) => state.user?.role || state.user?.role_slug || ''
  },

  actions: {
    async bootstrap() {
      const cachedUser = localStorage.getItem('sm_user')
      if (cachedUser) {
        try {
          this.user = JSON.parse(cachedUser)
        } catch {
          this.user = null
        }
      }

      if (localStorage.getItem('sm_access_token')) {
        try {
          const resp = await api.get('/auth/me')
          this.user = resp.data?.data || null
          if (this.user) {
            localStorage.setItem('sm_user', JSON.stringify(this.user))
          }
        } catch {
          this.clearSession()
        }
      }

      this.initialized = true
    },

    async login(email, password) {
      this.loading = true
      try {
        const resp = await api.post('/auth/login', { email, password })
        const data = resp.data?.data || {}

        localStorage.setItem('sm_access_token', data.access_token)
        localStorage.setItem('sm_refresh_token', data.refresh_token)

        const meResp = await api.get('/auth/me')
        this.user = meResp.data?.data || data.user || null
        if (this.user) {
          localStorage.setItem('sm_user', JSON.stringify(this.user))
        }
      } finally {
        this.loading = false
      }
    },

    async logout() {
      const refresh = localStorage.getItem('sm_refresh_token')
      try {
        await api.post('/auth/logout', refresh ? { refresh_token: refresh } : {})
      } catch {
        // Ignorar error de logout remoto
      }

      this.clearSession()
    },

    clearSession() {
      this.user = null
      localStorage.removeItem('sm_access_token')
      localStorage.removeItem('sm_refresh_token')
      localStorage.removeItem('sm_user')
    }
  }
})
