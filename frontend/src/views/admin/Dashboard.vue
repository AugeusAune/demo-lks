<template>
    <div class="space-y-6">
        <!-- Stat Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="stat-card">
                <div class="stat-icon bg-blue-100">
                    <CalendarIcon class="w-6 h-6 text-blue-600" />
                </div>
                <div>
                    <div class="stat-value">{{ stats.today_orders ?? '—' }}</div>
                    <div class="stat-label">Order Hari Ini</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-orange-100">
                    <ArrowPathIcon class="w-6 h-6 text-orange-600" />
                </div>
                <div>
                    <div class="stat-value">{{ stats.active_orders ?? '—' }}</div>
                    <div class="stat-label">Sedang Berjalan</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-green-100">
                    <CheckCircleIcon class="w-6 h-6 text-green-600" />
                </div>
                <div>
                    <div class="stat-value">{{ stats.done_this_month ?? '—' }}</div>
                    <div class="stat-label">Selesai Bulan Ini</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-purple-100">
                    <CurrencyDollarIcon class="w-6 h-6 text-purple-600" />
                </div>
                <div>
                    <div class="stat-value text-lg">{{ formatCurrency(stats.total_revenue) }}</div>
                    <div class="stat-label">Pendapatan Bulan Ini</div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <!-- Status Breakdown Pie -->
            <div class="card p-5">
                <h3 class="font-semibold text-gray-700 mb-4 text-sm">Status Order</h3>
                <div v-if="hasStatusData" class="h-44">
                    <Doughnut :data="pieData" :options="pieOptions" />
                </div>
                <p v-else class="text-gray-400 text-sm text-center py-10">Belum ada data</p>
            </div>

            <!-- Recent Transactions -->
            <div class="card col-span-1 lg:col-span-2">
                <div class="card-header">
                    <h3 class="font-semibold text-gray-700 text-sm">Transaksi Terbaru</h3>
                    <RouterLink
                        to="/admin/transactions"
                        class="text-xs text-blue-600 hover:underline"
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
                                <th>Teknisi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!stats.recent?.length">
                                <td colspan="5" class="text-center text-gray-400 py-8">
                                    Belum ada transaksi
                                </td>
                            </tr>
                            <tr v-for="trx in stats.recent" :key="trx.id">
                                <td>
                                    <RouterLink
                                        :to="`/admin/transactions/${trx.id}`"
                                        class="text-blue-600 hover:underline font-mono text-xs"
                                    >
                                        {{ trx.order_number }}
                                    </RouterLink>
                                </td>
                                <td>{{ trx.customer_name }}</td>
                                <td class="text-gray-500">
                                    {{ trx.detail?.product?.name || '-' }}
                                </td>
                                <td><StatusBadge :status="trx.status" /></td>
                                <td class="text-gray-500">{{ trx.technician?.name || '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { Doughnut } from 'vue-chartjs'
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js'
import api from '@/api/axios'
import StatusBadge from '@/components/StatusBadge.vue'
import {
    CalendarIcon,
    ArrowPathIcon,
    CheckCircleIcon,
    CurrencyDollarIcon,
} from '@heroicons/vue/24/outline'

ChartJS.register(ArcElement, Tooltip, Legend)

const stats = ref({})

const hasStatusData = computed(() => {
    const b = stats.value.status_breakdown
    return b && Object.values(b).some((v) => v > 0)
})

const statusColors = {
    received: '#3b82f6',
    diagnosa: '#f59e0b',
    perbaikan: '#f97316',
    selesai: '#22c55e',
    diambil: '#94a3b8',
}

const statusLabels = {
    received: 'Diterima',
    diagnosa: 'Diagnosa',
    perbaikan: 'Perbaikan',
    selesai: 'Selesai',
    diambil: 'Diambil',
}

const pieData = computed(() => {
    const b = stats.value.status_breakdown || {}
    return {
        labels: Object.keys(b).map((k) => statusLabels[k] || k),
        datasets: [
            {
                data: Object.values(b),
                backgroundColor: Object.keys(b).map((k) => statusColors[k] || '#ccc'),
                borderWidth: 0,
            },
        ],
    }
})

const pieOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 10 } } },
}

function formatCurrency(v) {
    if (!v) return 'Rp 0'
    return 'Rp ' + Number(v).toLocaleString('id-ID')
}

onMounted(async () => {
    const { data } = await api.get('/dashboard')
    stats.value = data.data
})
</script>
