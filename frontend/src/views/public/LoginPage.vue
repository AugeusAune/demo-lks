<template>
    <div
        class="min-h-screen bg-gradient-to-br from-blue-900 via-blue-800 to-blue-600 flex items-center justify-center p-4"
    >
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="text-center mb-8">
                <div
                    class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-xl"
                >
                    <span class="text-blue-700 text-2xl font-black">SC</span>
                </div>
                <h1 class="text-white text-2xl font-bold">Service Center</h1>
                <p class="text-blue-200 text-sm mt-1">Management System</p>
            </div>

            <!-- Card -->
            <div class="bg-white rounded-2xl shadow-2xl p-8">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Masuk ke Sistem</h2>

                <div v-if="errorMsg" class="alert-error mb-4">
                    <ExclamationCircleIcon class="w-4 h-4 flex-shrink-0 mt-0.5" />
                    {{ errorMsg }}
                </div>

                <form @submit.prevent="handleLogin" class="space-y-4">
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input
                            v-model="form.email"
                            type="email"
                            class="form-input"
                            placeholder="admin@service.com"
                            required
                            :disabled="loading"
                        />
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="relative">
                            <input
                                v-model="form.password"
                                :type="showPass ? 'text' : 'password'"
                                class="form-input pr-10"
                                placeholder="••••••••"
                                required
                                :disabled="loading"
                            />
                            <button
                                type="button"
                                @click="showPass = !showPass"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            >
                                <EyeIcon v-if="!showPass" class="w-4 h-4" />
                                <EyeSlashIcon v-else class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="btn-primary w-full justify-center py-2.5"
                        :disabled="loading"
                    >
                        <ArrowPathIcon v-if="loading" class="w-4 h-4 animate-spin" />
                        {{ loading ? 'Memproses...' : 'Masuk' }}
                    </button>
                </form>

                <div class="mt-6 pt-5 border-t border-gray-100">
                    <p class="text-xs text-gray-400 text-center mb-3">
                        Atau cek status order sebagai pelanggan
                    </p>
                    <RouterLink to="/tracking" class="btn-outline w-full justify-center text-sm">
                        <MagnifyingGlassIcon class="w-4 h-4" />
                        Tracking Order
                    </RouterLink>
                </div>
            </div>

            <!-- Demo accounts -->
            <!-- <div class="mt-4 bg-white/10 backdrop-blur rounded-xl p-4 text-white text-xs space-y-1">
                <p class="font-semibold mb-2 text-blue-100">Demo Akun:</p>
                <p>
                    Admin:
                    <span class="font-mono bg-white/20 px-1 rounded">admin@service.com</span> /
                    <span class="font-mono bg-white/20 px-1 rounded">password</span>
                </p>
                <p>
                    Teknisi:
                    <span class="font-mono bg-white/20 px-1 rounded">teknisi@service.com</span> /
                    <span class="font-mono bg-white/20 px-1 rounded">password</span>
                </p>
                <p>
                    Kasir:
                    <span class="font-mono bg-white/20 px-1 rounded">kasir@service.com</span> /
                    <span class="font-mono bg-white/20 px-1 rounded">password</span>
                </p>
            </div> -->
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter, useRoute, RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import {
    ExclamationCircleIcon,
    EyeIcon,
    EyeSlashIcon,
    ArrowPathIcon,
    MagnifyingGlassIcon,
} from '@heroicons/vue/24/outline'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

const form = ref({ email: '', password: '' })
const loading = ref(false)
const errorMsg = ref('')
const showPass = ref(false)

async function handleLogin() {
    loading.value = true
    errorMsg.value = ''
    try {
        const user = await auth.login(form.value.email, form.value.password)
        const redirect = route.query.redirect
        if (redirect) return router.push(redirect)
        if (user.role === 'technician') router.push('/technician')
        else router.push('/admin')
    } catch (e) {
        errorMsg.value = e.response?.data?.message || 'Login gagal. Coba lagi.'
    } finally {
        loading.value = false
    }
}
</script>
