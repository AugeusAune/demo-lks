import api from '@/api/axios'
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useAuthStore = defineStore('auth', () => {
    const token = ref(localStorage.getItem('token') || null)
    const user = ref(JSON.parse(localStorage.getItem('user') || 'null'))

    const isLoggedIn = computed(() => !!token.value)
    const isAdmin = computed(() => user.value?.role === 'admin')
    const isTechnician = computed(() => user.value?.role === 'technician')
    const isCashier = computed(() => user.value?.role === 'cashier')

    async function login(email: string, password: string) {
        const { data } = await api.post('/auth/login', { email, password })
        token.value = data.token
        user.value = data.user
        localStorage.setItem('token', data.token)
        localStorage.setItem('user', JSON.stringify(data.user))
        return data.user
    }

    async function logout() {
        try {
            await api.post('/auth/logout')
        } catch {}
        token.value = null
        user.value = null
        localStorage.removeItem('token')
        localStorage.removeItem('user')
    }

    async function fetchMe() {
        const { data } = await api.get('/auth/me')
        user.value = data.user
        localStorage.setItem('user', JSON.stringify(data.user))
    }

    return { token, user, isLoggedIn, isAdmin, isTechnician, isCashier, login, logout, fetchMe }
})
