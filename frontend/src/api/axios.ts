import axios from 'axios'

const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    timeout: 15000,
})

// Request interceptor — attach JWT token
api.interceptors.request.use((config) => {
    const token = localStorage.getItem('token')
    if (token) config.headers.Authorization = `Bearer ${token}`
    return config
})

// Response interceptor — handle token expiry
api.interceptors.response.use(
    (res) => res,
    async (err) => {
        const original = err.config
        if (
            err.response?.status === 401 &&
            err.response?.data?.code === 'TOKEN_EXPIRED' &&
            !original._retry
        ) {
            original._retry = true
            try {
                const { data } = await api.post('/auth/refresh')
                localStorage.setItem('token', data.token)
                original.headers.Authorization = `Bearer ${data.token}`
                return api(original)
            } catch {
                localStorage.removeItem('token')
                localStorage.removeItem('user')
                window.location.href = '/login'
            }
        }
        if (err.response?.status === 401) {
            localStorage.removeItem('token')
            localStorage.removeItem('user')
            window.location.href = '/login'
        }
        return Promise.reject(err)
    },
)

export default api
