<template>
    <div class="space-y-6">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="stat-card">
                <div class="stat-icon bg-blue-100">
                    <ClipboardDocumentListIcon class="w-6 h-6 text-blue-600" />
                </div>
                <div>
                    <div class="stat-value">{{ stats.total_orders ?? '—' }}</div>
                    <div class="stat-label">Total Order</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-orange-100">
                    <ArrowPathIcon class="w-6 h-6 text-orange-600" />
                </div>
                <div>
                    <div class="stat-value">{{ stats.active_orders ?? '—' }}</div>
                    <div class="stat-label">Aktif</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-green-100">
                    <CheckCircleIcon class="w-6 h-6 text-green-600" />
                </div>
                <div>
                    <div class="stat-value">{{ stats.done_orders ?? '—' }}</div>
                    <div class="stat-label">Selesai</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-purple-100">
                    <CalendarIcon class="w-6 h-6 text-purple-600" />
                </div>
                <div>
                    <div class="stat-value">{{ stats.today_orders ?? '—' }}</div>
                    <div class="stat-label">Hari Ini</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-gray-700 text-sm">Order Terbaru Saya</h3>
                <RouterLink to="/technician/orders" class="text-xs text-blue-600 hover:underline"
                    >Lihat Semua →</RouterLink
                >
            </div>
            <div class="table-wrapper rounded-none border-0">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No Order</th>
                            <th>Pelanggan</th>
                            <th>Produk</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!stats.recent?.length">
                            <td colspan="5" class="text-center py-10 text-gray-400">
                                Belum ada order
                            </td>
                        </tr>
                        <tr v-for="t in stats.recent" :key="t.id">
                            <td>
                                <span class="font-mono text-xs text-blue-600">{{
                                    t.order_number
                                }}</span>
                            </td>
                            <td>{{ t.customer_name }}</td>
                            <td class="text-gray-500 text-sm">
                                {{ t.detail?.product?.name || '-' }}
                            </td>
                            <td><StatusBadge :status="t.status" /></td>
                            <td>
                                <RouterLink
                                    :to="`/technician/orders/${t.id}`"
                                    class="btn-outline btn-sm"
                                    ><EyeIcon class="w-3.5 h-3.5"
                                /></RouterLink>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/api/axios'
import StatusBadge from '@/components/StatusBadge.vue'
import {
    ClipboardDocumentListIcon,
    ArrowPathIcon,
    CheckCircleIcon,
    CalendarIcon,
    EyeIcon,
} from '@heroicons/vue/24/outline'

const stats = ref({})
onMounted(async () => {
    const { data } = await api.get('/dashboard')
    stats.value = data.data
})
</script>
