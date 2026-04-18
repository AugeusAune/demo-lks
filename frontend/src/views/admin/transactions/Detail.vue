<template>
    <div v-if="loading" class="flex items-center justify-center py-20 text-gray-400">
        <ArrowPathIcon class="w-6 h-6 animate-spin mr-2" /> Memuat...
    </div>

    <div v-else-if="trx" class="space-y-4 max-w-full">
        <!-- Header Actions -->
        <div class="flex items-center gap-3">
            <button @click="$router.back()" class="btn-secondary btn-sm">← Kembali</button>
            <span class="font-mono text-blue-600 font-semibold">{{ trx.order_number }}</span>
            <StatusBadge :status="trx.status" />
            <div class="ml-auto flex gap-2">
                <RouterLink :to="`/admin/transactions/${trx.id}/edit`" class="btn-outline btn-sm">
                    <PencilIcon class="w-3.5 h-3.5" /> Edit
                </RouterLink>
                <button class="btn-success btn-sm" @click="downloadInvoice">
                    <DocumentArrowDownIcon class="w-3.5 h-3.5" /> Invoice PDF
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <!-- Left: main info -->
            <div class="lg:col-span-2 space-y-4">
                <!-- Customer & Order Info -->
                <div class="card p-5">
                    <h3 class="font-semibold text-gray-700 mb-4 text-sm uppercase tracking-wide">
                        Informasi Order
                    </h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-400 text-xs mb-0.5">Pelanggan</p>
                            <p class="font-semibold">{{ trx.customer_name }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs mb-0.5">No. Telepon</p>
                            <p class="font-semibold">{{ trx.customer_phone }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs mb-0.5">Email</p>
                            <p class="font-semibold">{{ trx.customer_email || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs mb-0.5">Tanggal Masuk</p>
                            <p class="font-semibold">{{ formatDate(trx.received_date) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs mb-0.5">Tanggal Selesai</p>
                            <p class="font-semibold">{{ formatDate(trx.completed_date) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs mb-0.5">Teknisi</p>
                            <p class="font-semibold">{{ trx.technician?.name || '—' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Service Detail -->
                <div class="card p-5">
                    <h3 class="font-semibold text-gray-700 mb-4 text-sm uppercase tracking-wide">
                        Detail Servis
                    </h3>
                    <div class="space-y-3 text-sm">
                        <div>
                            <p class="text-gray-400 text-xs mb-0.5">Produk</p>
                            <p class="font-semibold">
                                {{ trx.detail?.product?.name || '-' }}
                                <span class="ml-1 badge badge-blue text-xs">{{
                                    trx.detail?.product?.category
                                }}</span>
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs mb-0.5">Keluhan</p>
                            <p class="text-gray-700 bg-gray-50 p-2 rounded-lg">
                                {{ trx.detail?.complaint || '-' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs mb-0.5">Diagnosa</p>
                            <p class="text-gray-700 bg-gray-50 p-2 rounded-lg">
                                {{ trx.detail?.diagnosis || '—' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs mb-0.5">Catatan Perbaikan</p>
                            <p class="text-gray-700 bg-gray-50 p-2 rounded-lg">
                                {{ trx.detail?.repair_notes || '—' }}
                            </p>
                        </div>
                        <div class="grid grid-cols-2 gap-3 pt-2 border-t border-gray-100">
                            <div>
                                <p class="text-gray-400 text-xs mb-0.5">Estimasi Biaya</p>
                                <p class="font-semibold">
                                    {{ formatCurrency(trx.detail?.estimated_cost) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-400 text-xs mb-0.5">Biaya Aktual</p>
                                <p class="font-bold text-blue-700">
                                    {{ formatCurrency(trx.detail?.actual_cost) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: status update + history -->
            <div class="space-y-4">
                <!-- Update Status -->
                <div v-if="trx.status !== 'diambil'" class="card p-5">
                    <h3 class="font-semibold text-gray-700 mb-4 text-sm uppercase tracking-wide">
                        Update Status
                    </h3>
                    <div class="space-y-3">
                        <div class="form-group">
                            <label class="form-label">Status Baru</label>
                            <select v-model="statusForm.status" class="form-select">
                                <option value="received">Diterima</option>
                                <option value="diagnosa">Diagnosa</option>
                                <option value="perbaikan">Perbaikan</option>
                                <option value="selesai">Selesai</option>
                                <option value="diambil">Diambil</option>
                                <option value="batal">Batal / Tidak Bisa</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Catatan</label>
                            <textarea
                                v-model="statusForm.notes"
                                class="form-input"
                                rows="2"
                                placeholder="Catatan perubahan status..."
                            />
                        </div>
                        <button
                            @click="updateStatus"
                            class="btn-primary w-full justify-center"
                            :disabled="updating"
                        >
                            <ArrowPathIcon v-if="updating" class="w-4 h-4 animate-spin" />
                            {{ updating ? 'Memproses...' : 'Update Status' }}
                        </button>
                    </div>
                </div>

                <!-- Jika Sudah Diambil -->
                <div v-else class="card p-6 bg-gray-50 border border-gray-100 flex flex-col items-center text-center">
                    <div class="w-12 h-12 bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-800 mb-1">Pekerjaan Selesai</h3>
                    <p class="text-sm text-gray-500">
                        Barang ini sudah dikembalikan kepada pelanggan pada {{ formatDate(trx.completed_date) }}. Status tidak dapat diubah lagi.
                    </p>
                </div>

                <!-- Status History -->
                <div class="card p-5">
                    <h3 class="font-semibold text-gray-700 mb-4 text-sm uppercase tracking-wide">
                        Riwayat Status
                    </h3>
                    <div class="space-y-3 max-h-80 overflow-y-auto">
                        <div v-for="h in trx.status_histories" :key="h.id" class="flex gap-3">
                            <div class="flex flex-col items-center">
                                <div
                                    class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0"
                                >
                                    <div class="w-2 h-2 bg-blue-500 rounded-full" />
                                </div>
                                <div class="w-px bg-gray-200 flex-1 mt-1" />
                            </div>
                            <div class="pb-3 flex-1 min-w-0">
                                <p class="text-xs font-semibold text-gray-700">
                                    {{ statusLabel(h.status) }}
                                </p>
                                <p v-if="h.notes" class="text-xs text-gray-500 mt-0.5">
                                    {{ h.notes }}
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ h.changed_by?.name }} · {{ formatDateTime(h.created_at) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <Toast ref="toast" />
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import api from '@/api/axios'
import StatusBadge from '@/components/StatusBadge.vue'
import Toast from '@/components/Toast.vue'
import { ArrowPathIcon, PencilIcon, DocumentArrowDownIcon } from '@heroicons/vue/24/outline'

const route = useRoute()
const trx = ref(null)
const loading = ref(true)
const updating = ref(false)
const toast = ref(null)

const statusForm = reactive({ status: 'received', notes: '' })

async function load() {
    loading.value = true
    try {
        const { data } = await api.get(`/transactions/${route.params.id}`)
        trx.value = data.data
        statusForm.status = trx.value.status
    } finally {
        loading.value = false
    }
}

async function updateStatus() {
    updating.value = true
    try {
        await api.patch(`/transactions/${trx.value.id}/status`, statusForm)
        toast.value.add('Status berhasil diupdate', 'success')
        load()
    } catch (e) {
        toast.value.add(e.response?.data?.message || 'Gagal update status', 'error')
    } finally {
        updating.value = false
    }
}

async function downloadInvoice() {
    try {
        const res = await api.get(`/transactions/${trx.value.id}/invoice`, { responseType: 'blob' })
        const url = URL.createObjectURL(new Blob([res.data], { type: 'application/pdf' }))
        const a = document.createElement('a')
        a.href = url
        a.download = `invoice-${trx.value.order_number}.pdf`
        a.click()
        URL.revokeObjectURL(url)
    } catch {
        toast.value.add('Gagal download invoice', 'error')
    }
}

const statusLabel = (s) =>
    ({
        received: 'Diterima',
        diagnosa: 'Diagnosa',
        perbaikan: 'Perbaikan',
        selesai: 'Selesai',
        diambil: 'Diambil',
        batal: 'Batal',
    })[s] || s
const formatCurrency = (v) => (v ? 'Rp ' + Number(v).toLocaleString('id-ID') : 'Rp 0')
const formatDate = (d) =>
    d
        ? new Date(d).toLocaleDateString('id-ID', {
              day: 'numeric',
              month: 'long',
              year: 'numeric',
          })
        : '-'
const formatDateTime = (d) =>
    d
        ? new Date(d).toLocaleString('id-ID', {
              day: 'numeric',
              month: 'short',
              year: 'numeric',
              hour: '2-digit',
              minute: '2-digit',
          })
        : '-'

onMounted(load)
</script>
