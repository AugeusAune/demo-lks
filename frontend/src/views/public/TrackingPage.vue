<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-900 via-blue-800 to-blue-600">
        <!-- Header -->
        <header class="px-6 py-4 flex items-center justify-between max-w-4xl mx-auto">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-white rounded-xl flex items-center justify-center">
                    <span class="text-blue-700 font-black text-sm">SC</span>
                </div>
                <span class="text-white font-bold">Service Center</span>
            </div>
            <RouterLink
                to="/login"
                class="text-blue-200 hover:text-white text-sm flex items-center gap-1 transition-colors"
            >
                <LockClosedIcon class="w-4 h-4" /> Staff Login
            </RouterLink>
        </header>

        <!-- Hero Search -->
        <div class="max-w-2xl mx-auto px-4 pt-12 pb-8 text-center">
            <h1 class="text-3xl font-bold text-white mb-2">Cek Status Servis</h1>
            <p class="text-blue-200 mb-8">Masukkan nomor order untuk melihat progres servis Anda</p>

            <div class="flex gap-2">
                <input
                    v-model="orderNumber"
                    type="text"
                    class="form-input flex-1 text-base py-3"
                    placeholder="Contoh: SVC-20250418-001"
                    @keyup.enter="search"
                    :disabled="loading"
                />
                <button
                    @click="search"
                    class="btn-primary px-6 py-3 text-base"
                    :disabled="loading || !orderNumber"
                >
                    <ArrowPathIcon v-if="loading" class="w-5 h-5 animate-spin" />
                    <MagnifyingGlassIcon v-else class="w-5 h-5" />
                    Cek
                </button>
            </div>

            <p
                v-if="notFound"
                class="mt-4 text-red-300 text-sm flex items-center justify-center gap-1"
            >
                <ExclamationCircleIcon class="w-4 h-4" /> Nomor order tidak ditemukan
            </p>
        </div>

        <!-- Result -->
        <div v-if="result" class="max-w-full mx-auto px-4 pb-12 space-y-4">
            <!-- Info Card -->
            <div class="bg-white rounded-2xl p-6 shadow-xl">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <span class="text-xs text-gray-400 uppercase tracking-wide"
                            >Nomor Order</span
                        >
                        <h2 class="text-xl font-bold text-gray-800">{{ result.order_number }}</h2>
                    </div>
                    <span :class="statusClass(result.status)" class="badge text-sm px-3 py-1">{{
                        result.status_label
                    }}</span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <p class="text-gray-400 text-xs mb-0.5">Nama Pelanggan</p>
                        <p class="font-semibold text-gray-800">{{ result.customer_name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs mb-0.5">Produk</p>
                        <p class="font-semibold text-gray-800">{{ result.product?.name || '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs mb-0.5">Tanggal Masuk</p>
                        <p class="font-semibold text-gray-800">
                            {{ formatDate(result.received_date) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs mb-0.5">Teknisi</p>
                        <p class="font-semibold text-gray-800">
                            {{ result.technician_name || '-' }}
                        </p>
                    </div>
                </div>

                <div v-if="result.complaint" class="mt-4 p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-400 mb-1">Keluhan</p>
                    <p class="text-sm text-gray-700">{{ result.complaint }}</p>
                </div>
            </div>

            <!-- Stepper Progress -->
            <div class="bg-white rounded-2xl p-6 shadow-xl">
                <h3 class="font-semibold text-gray-800 mb-6">Progres Servis</h3>
                <div class="flex items-center justify-between relative">
                    <!-- connecting line -->
                    <div class="absolute top-5 left-0 right-0 h-0.5 bg-gray-200 z-0" />
                    <div
                        class="absolute top-5 left-0 h-0.5 bg-blue-500 z-0 transition-all duration-700"
                        :style="{ width: progressWidth }"
                    />

                    <div
                        v-for="step in steps"
                        :key="step.key"
                        class="flex flex-col items-center gap-2 relative z-10"
                    >
                        <div
                            :class="[
                                'w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all duration-300',
                                isStepDone(step.key)
                                    ? 'bg-blue-600 border-blue-600'
                                    : isStepActive(step.key)
                                      ? 'bg-white border-blue-500 ring-4 ring-blue-100'
                                      : 'bg-white border-gray-300',
                            ]"
                        >
                            <CheckIcon v-if="isStepDone(step.key)" class="w-5 h-5 text-white" />
                            <ArrowPathIcon
                                v-else-if="isStepActive(step.key)"
                                class="w-4 h-4 text-blue-500 animate-spin"
                            />
                            <span v-else class="text-gray-400 text-xs font-bold">{{
                                step.num
                            }}</span>
                        </div>
                        <span
                            :class="[
                                'text-xs font-medium text-center',
                                isStepDone(step.key) || isStepActive(step.key)
                                    ? 'text-blue-700'
                                    : 'text-gray-400',
                            ]"
                        >
                            {{ step.label }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- History Timeline -->
            <div class="bg-white rounded-2xl p-6 shadow-xl">
                <h3 class="font-semibold text-gray-800 mb-4">Riwayat Status</h3>
                <div class="space-y-4">
                    <div
                        v-for="h in result.status_histories"
                        :key="h.changed_at"
                        class="flex gap-4"
                    >
                        <div class="flex flex-col items-center">
                            <div
                                :class="[
                                    'w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0',
                                    statusClass(h.status),
                                ]"
                            >
                                <ClockIcon class="w-4 h-4" />
                            </div>
                            <div class="w-0.5 bg-gray-200 flex-1 mt-1" />
                        </div>
                        <div class="pb-4 flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-semibold text-sm text-gray-800">{{
                                    h.status_label
                                }}</span>
                                <span class="text-xs text-gray-400">oleh {{ h.changed_by }}</span>
                            </div>
                            <p v-if="h.notes" class="text-sm text-gray-600">{{ h.notes }}</p>
                            <p class="text-xs text-gray-400 mt-1">
                                {{ formatDateTime(h.changed_at) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/api/axios'
import {
    MagnifyingGlassIcon,
    ArrowPathIcon,
    ExclamationCircleIcon,
    LockClosedIcon,
    CheckIcon,
    ClockIcon,
} from '@heroicons/vue/24/outline'

const orderNumber = ref('')
const loading = ref(false)
const result = ref(null)
const notFound = ref(false)

const steps = [
    { key: 'received', label: 'Diterima', num: 1 },
    { key: 'diagnosa', label: 'Diagnosa', num: 2 },
    { key: 'perbaikan', label: 'Perbaikan', num: 3 },
    { key: 'selesai', label: 'Selesai', num: 4 },
    { key: 'diambil', label: 'Diambil', num: 5 },
]

const stepOrder = steps.map((s) => s.key)

const currentStepIndex = computed(() => {
    if (!result.value) return -1
    return stepOrder.indexOf(result.value.status)
})

const progressWidth = computed(() => {
    const idx = currentStepIndex.value
    if (idx < 0) return '0%'
    return `${(idx / (steps.length - 1)) * 100}%`
})

function isStepDone(key) {
    if (result.value && ['selesai', 'diambil'].includes(result.value.status) && key === result.value.status) {
        return true
    }
    return stepOrder.indexOf(key) < currentStepIndex.value
}

function isStepActive(key) {
    if (result.value && ['selesai', 'diambil'].includes(result.value.status) && key === result.value.status) {
        return false
    }
    return stepOrder.indexOf(key) === currentStepIndex.value
}

async function search() {
    if (!orderNumber.value.trim()) return
    loading.value = true
    notFound.value = false
    result.value = null
    try {
        const { data } = await api.get(`/tracking/${orderNumber.value.trim()}`)
        result.value = data.data
    } catch (e) {
        if (e.response?.status === 404) notFound.value = true
    } finally {
        loading.value = false
    }
}

function statusClass(status) {
    return (
        {
            received: 'badge-blue',
            diagnosa: 'badge-yellow',
            perbaikan: 'badge-orange',
            selesai: 'badge-green',
            diambil: 'badge-gray',
        }[status] || 'badge-gray'
    )
}

function formatDate(d) {
    if (!d) return '-'
    return new Date(d).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    })
}

function formatDateTime(d) {
    if (!d) return '-'
    return new Date(d).toLocaleString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}
</script>
