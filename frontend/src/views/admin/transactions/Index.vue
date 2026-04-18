<template>
    <div class="space-y-4">
        <!-- Toolbar -->
        <div class="flex flex-wrap gap-3">
            <input
                v-model="filters.search"
                type="text"
                class="form-input w-56"
                placeholder="No order / pelanggan..."
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
            <input
                v-model="filters.date_from"
                type="date"
                class="form-input w-40"
                @change="fetch"
            />
            <input v-model="filters.date_to" type="date" class="form-input w-40" @change="fetch" />
            <RouterLink to="/admin/transactions/create" class="btn-primary ml-auto">
                <PlusIcon class="w-4 h-4" /> Buat Transaksi
            </RouterLink>
        </div>

        <!-- Table -->
        <div class="card">
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No Order</th>
                            <th>Pelanggan</th>
                            <th>Produk</th>
                            <th>Teknisi</th>
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
                                Tidak ada transaksi
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
                            <td class="text-sm text-gray-600">
                                {{ t.detail?.product?.name || '-' }}
                            </td>
                            <td class="text-sm text-gray-600">{{ t.technician?.name || '-' }}</td>
                            <td class="text-sm text-gray-500">{{ formatDate(t.received_date) }}</td>
                            <td><StatusBadge :status="t.status" /></td>
                            <td>
                                <div class="flex gap-1">
                                    <RouterLink
                                        :to="`/admin/transactions/${t.id}`"
                                        class="btn-outline btn-sm"
                                        title="Detail"
                                    >
                                        <EyeIcon class="w-3.5 h-3.5" />
                                    </RouterLink>
                                    <RouterLink
                                        :to="`/admin/transactions/${t.id}/edit`"
                                        class="btn-outline btn-sm"
                                        title="Edit"
                                    >
                                        <PencilIcon class="w-3.5 h-3.5" />
                                    </RouterLink>
                                    <button
                                        class="btn-outline btn-sm text-green-600 border-green-200 hover:bg-green-50"
                                        title="Download Invoice"
                                        @click="downloadInvoice(t)"
                                    >
                                        <DocumentArrowDownIcon class="w-3.5 h-3.5" />
                                    </button>
                                    <button
                                        v-if="isAdmin"
                                        class="btn-danger btn-sm"
                                        title="Hapus"
                                        @click="confirmDelete(t)"
                                    >
                                        <TrashIcon class="w-3.5 h-3.5" />
                                    </button>
                                </div>
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

        <ConfirmModal
            :show="showConfirm"
            title="Hapus Transaksi"
            :message="`Hapus transaksi '${deleteTarget?.order_number}'?`"
            :loading="deleting"
            @confirm="doDelete"
            @cancel="showConfirm = false"
        />

        <Toast ref="toast" />
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/api/axios'
import { useAuthStore } from '@/stores/auth'
import StatusBadge from '@/components/StatusBadge.vue'
import Pagination from '@/components/Pagination.vue'
import ConfirmModal from '@/components/ConfirmModal.vue'
import Toast from '@/components/Toast.vue'
import {
    PlusIcon,
    PencilIcon,
    TrashIcon,
    ArrowPathIcon,
    EyeIcon,
    DocumentArrowDownIcon,
} from '@heroicons/vue/24/outline'

const auth = useAuthStore()
const isAdmin = computed(() => auth.isAdmin)
const transactions = ref({})
const loading = ref(false)
const showConfirm = ref(false)
const deleting = ref(false)
const deleteTarget = ref(null)
const toast = ref(null)

const filters = reactive({ search: '', status: '', date_from: '', date_to: '', page: 1 })

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

async function downloadInvoice(t) {
    try {
        const res = await api.get(`/transactions/${t.id}/invoice`, { responseType: 'blob' })
        const url = URL.createObjectURL(new Blob([res.data], { type: 'application/pdf' }))
        const a = document.createElement('a')
        a.href = url
        a.download = `invoice-${t.order_number}.pdf`
        a.click()
        URL.revokeObjectURL(url)
    } catch {
        toast.value.add('Gagal download invoice', 'error')
    }
}

function confirmDelete(t) {
    deleteTarget.value = t
    showConfirm.value = true
}

async function doDelete() {
    deleting.value = true
    try {
        await api.delete(`/transactions/${deleteTarget.value.id}`)
        toast.value.add('Transaksi dihapus', 'success')
        showConfirm.value = false
        fetch()
    } catch (e) {
        toast.value.add(e.response?.data?.message || 'Gagal', 'error')
    } finally {
        deleting.value = false
    }
}

function formatDate(d) {
    return d
        ? new Date(d).toLocaleDateString('id-ID', {
              day: 'numeric',
              month: 'short',
              year: 'numeric',
          })
        : '-'
}

onMounted(fetch)
</script>
