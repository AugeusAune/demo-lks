<template>
    <div v-if="loading" class="flex items-center justify-center py-20 text-gray-400">
        <ArrowPathIcon class="w-6 h-6 animate-spin mr-2" /> Memuat...
    </div>

    <div v-else-if="trx" class="space-y-4 max-w-full">
        <div class="flex items-center gap-3">
            <button @click="$router.back()" class="btn-secondary btn-sm">← Kembali</button>
            <span class="font-mono text-blue-600 font-semibold">{{ trx.order_number }}</span>
            <StatusBadge :status="trx.status" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <!-- Info -->
            <div class="card p-5 space-y-4">
                <div>
                    <h3 class="font-semibold text-gray-700 mb-3 text-sm uppercase tracking-wide">
                        Data Pelanggan & Produk
                    </h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Pelanggan</span
                            ><span class="font-semibold">{{ trx.customer_name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Telepon</span
                            ><span>{{ trx.customer_phone }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Tgl Masuk</span
                            ><span>{{ formatDate(trx.received_date) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Produk</span
                            ><span class="font-semibold">{{
                                trx.detail?.product?.name || '-'
                            }}</span>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-4">
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-2">
                        Keluhan Pelanggan
                    </p>
                    <p class="text-sm bg-orange-50 text-orange-800 p-3 rounded-lg">
                        {{ trx.detail?.complaint || '-' }}
                    </p>
                </div>

                <div v-if="trx.detail?.diagnosis" class="border-t border-gray-100 pt-4">
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-2">Diagnosa</p>
                    <p class="text-sm bg-blue-50 text-blue-800 p-3 rounded-lg">
                        {{ trx.detail.diagnosis }}
                    </p>
                </div>

                <div v-if="trx.detail?.repair_notes" class="border-t border-gray-100 pt-4">
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-2">
                        Catatan Perbaikan
                    </p>
                    <p class="text-sm bg-green-50 text-green-800 p-3 rounded-lg">
                        {{ trx.detail.repair_notes }}
                    </p>
                </div>
            </div>

            <!-- Right column -->
            <div class="space-y-4">
                <!-- Update Status -->
                <div v-if="!['diambil', 'batal'].includes(trx.status)" class="card p-5">
                    <h3 class="font-semibold text-gray-700 mb-4 text-sm uppercase tracking-wide">
                        Update Progress
                    </h3>
                    <div class="space-y-3">
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select v-model="statusForm.status" class="form-select">
                                <option value="received">Diterima</option>
                                <option value="diagnosa">Diagnosa</option>
                                <option value="perbaikan">Sedang Diperbaiki</option>
                                <option value="selesai">Selesai</option>
                            </select>
                        </div>
                        <div v-if="['selesai', 'perbaikan'].includes(statusForm.status)" class="form-group">
                            <label class="form-label">Total Biaya Aktual (Rp)</label>
                            <input
                                v-model.number="statusForm.actual_cost"
                                type="number"
                                class="form-input"
                                placeholder="0"
                            />
                        </div>
                        <div class="form-group">
                            <label class="form-label">Catatan Progress</label>
                            <textarea
                                v-model="statusForm.notes"
                                class="form-input"
                                rows="3"
                                placeholder="Apa yang sudah dikerjakan? Apa kendalanya?"
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

                <!-- Jika Sudah Diambil atau Batal -->
                <div v-else class="card p-6 bg-gray-50 border border-gray-100 flex flex-col items-center text-center">
                    <div v-if="trx.status === 'diambil'" class="w-12 h-12 bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                    </div>
                    <div v-else class="w-12 h-12 bg-red-100 text-red-600 rounded-full flex items-center justify-center mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-800 mb-1">{{ trx.status === 'batal' ? 'Transaksi Dibatalkan' : 'Pekerjaan Selesai' }}</h3>
                    <p class="text-sm text-gray-500">
                        {{ trx.status === 'batal' ? 'Transaksi ini telah dibatalkan. Progress tidak dapat diubah lagi.' : 'Barang ini sudah dikembalikan dan diambil oleh pelanggan. Progress tidak dapat diubah lagi.' }}
                    </p>
                </div>

                <!-- History -->
                <div class="card p-5">
                    <h3 class="font-semibold text-gray-700 mb-4 text-sm uppercase tracking-wide">
                        Riwayat
                    </h3>
                    <div class="space-y-3 max-h-60 overflow-y-auto">
                        <div v-for="h in trx.status_histories" :key="h.id" class="flex gap-3">
                            <div class="flex flex-col items-center">
                                <div
                                    class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0"
                                >
                                    <div class="w-2 h-2 bg-blue-500 rounded-full" />
                                </div>
                                <div class="w-px bg-gray-200 flex-1 mt-1" />
                            </div>
                            <div class="pb-3 flex-1">
                                <p class="text-xs font-semibold text-gray-700">
                                    {{ statusLabel(h.status) }}
                                </p>
                                <p v-if="h.notes" class="text-xs text-gray-500 mt-0.5">
                                    {{ h.notes }}
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ formatDateTime(h.created_at) }}
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
import { useRoute } from 'vue-router'
import api from '@/api/axios'
import StatusBadge from '@/components/StatusBadge.vue'
import Toast from '@/components/Toast.vue'
import { ArrowPathIcon } from '@heroicons/vue/24/outline'

const route = useRoute()
const trx = ref(null)
const loading = ref(true)
const updating = ref(false)
const toast = ref(null)
const statusForm = reactive({ status: 'received', notes: '', actual_cost: null })

async function load() {
    loading.value = true
    try {
        const { data } = await api.get(`/transactions/${route.params.id}`)
        trx.value = data.data
        statusForm.status = trx.value.status
        statusForm.actual_cost = trx.value.detail?.actual_cost || null
    } finally {
        loading.value = false
    }
}

async function updateStatus() {
    updating.value = true
    try {
        await api.patch(`/transactions/${trx.value.id}/status`, statusForm)
        toast.value.add('Status berhasil diupdate!', 'success')
        load()
    } catch (e) {
        toast.value.add(e.response?.data?.message || 'Gagal', 'error')
    } finally {
        updating.value = false
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
              hour: '2-digit',
              minute: '2-digit',
          })
        : '-'

onMounted(load)
</script>
