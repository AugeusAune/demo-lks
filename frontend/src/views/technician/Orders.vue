<template>
    <div class="space-y-4">
        <div class="flex flex-wrap gap-3">
            <input
                v-model="filters.search"
                type="text"
                class="form-input w-56"
                placeholder="Cari order..."
                @input="fetchDebounced"
            />
            <select v-model="filters.status" class="form-select w-40" @change="fetch">
                <option value="">Semua Status</option>
                <option value="received">Diterima</option>
                <option value="diagnosa">Diagnosa</option>
                <option value="perbaikan">Perbaikan</option>
                <option value="selesai">Selesai</option>
                <option value="diambil">Diambil</option>
            </select>
        </div>

        <div class="card">
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No Order</th>
                            <th>Pelanggan</th>
                            <th>Produk</th>
                            <th>Keluhan</th>
                            <th>Tgl Masuk</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loading">
                            <td colspan="7" class="text-center py-10 text-gray-400">
                                <ArrowPathIcon class="w-5 h-5 animate-spin mx-auto" />
                            </td>
                        </tr>
                        <tr v-else-if="!transactions.data?.length">
                            <td colspan="7" class="text-center py-10 text-gray-400">
                                Tidak ada order
                            </td>
                        </tr>
                        <tr v-for="t in transactions.data" :key="t.id">
                            <td>
                                <span class="font-mono text-xs text-blue-600">{{
                                    t.order_number
                                }}</span>
                            </td>
                            <td>
                                <p class="font-medium">{{ t.customer_name }}</p>
                                <p class="text-xs text-gray-400">{{ t.customer_phone }}</p>
                            </td>
                            <td class="text-sm">{{ t.detail?.product?.name || '-' }}</td>
                            <td class="text-sm text-gray-500 max-w-xs truncate">
                                {{ t.detail?.complaint || '-' }}
                            </td>
                            <td class="text-sm text-gray-500">{{ formatDate(t.received_date) }}</td>
                            <td><StatusBadge :status="t.status" /></td>
                            <td>
                                <RouterLink
                                    :to="`/technician/orders/${t.id}`"
                                    class="btn-primary btn-sm"
                                >
                                    <EyeIcon class="w-3.5 h-3.5" /> Buka
                                </RouterLink>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-100">
                <Pagination
                    :meta="transactions.meta"
                    @change="
                        (p) => {
                            filters.page = p
                            fetch()
                        }
                    "
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/api/axios'
import StatusBadge from '@/components/StatusBadge.vue'
import Pagination from '@/components/Pagination.vue'
import { ArrowPathIcon, EyeIcon } from '@heroicons/vue/24/outline'

const transactions = ref({})
const loading = ref(false)
const filters = reactive({ search: '', status: '', page: 1 })

let debounceTimer = null
function fetchDebounced() {
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(fetch, 350)
}

async function fetch() {
    loading.value = true
    try {
        const { data } = await api.get('/transactions', { params: filters })
        transactions.value = data.data
    } finally {
        loading.value = false
    }
}

const formatDate = (d) =>
    d
        ? new Date(d).toLocaleDateString('id-ID', {
              day: 'numeric',
              month: 'short',
              year: 'numeric',
          })
        : '-'
onMounted(fetch)
</script>
