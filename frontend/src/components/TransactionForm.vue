<template>
    <div class="max-w-full space-y-6">
        <div class="card">
            <div class="card-header">
                <h2 class="font-semibold text-gray-800">
                    {{ isEdit ? 'Edit Transaksi' : 'Buat Transaksi Baru' }}
                </h2>
                <span v-if="isEdit" class="font-mono text-sm text-blue-600">{{
                    form.order_number
                }}</span>
            </div>
            <form @submit.prevent="save" class="card-body space-y-6">
                <!-- Customer -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-3">
                        Data Pelanggan
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label">Nama Pelanggan *</label>
                            <input v-model="form.customer_name" class="form-input" required />
                            <p v-if="errors.customer_name" class="form-error">
                                {{ errors.customer_name[0] }}
                            </p>
                        </div>
                        <div class="form-group">
                            <label class="form-label">No. Telepon *</label>
                            <input v-model="form.customer_phone" class="form-input" required />
                            <p v-if="errors.customer_phone" class="form-error">
                                {{ errors.customer_phone[0] }}
                            </p>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email Pelanggan</label>
                            <input
                                v-model="form.customer_email"
                                type="email"
                                class="form-input"
                                placeholder="opsional, untuk notifikasi"
                            />
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tanggal Masuk *</label>
                            <input
                                v-model="form.received_date"
                                type="date"
                                class="form-input"
                                required
                            />
                        </div>
                    </div>
                </div>

                <!-- Assignment -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-3">
                        Penugasan Teknisi
                    </h3>
                    <div class="form-group max-w-sm">
                        <label class="form-label">Teknisi</label>
                        <select v-model="form.technician_id" class="form-select">
                            <option value="">— Belum ditugaskan —</option>
                            <option v-for="t in technicians" :key="t.id" :value="t.id">
                                {{ t.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Detail -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-3">
                        Detail Servis
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label">Produk *</label>
                            <select v-model="form.detail.product_id" class="form-select" required>
                                <option value="">Pilih produk...</option>
                                <option v-for="p in products" :key="p.id" :value="p.id">
                                    {{ p.name }} ({{ p.category }})
                                </option>
                            </select>
                            <p v-if="errors['detail.product_id']" class="form-error">
                                {{ errors['detail.product_id'][0] }}
                            </p>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Estimasi Biaya</label>
                            <input
                                v-model="form.detail.estimated_cost"
                                type="number"
                                class="form-input"
                                min="0"
                                step="1000"
                                placeholder="0"
                            />
                        </div>
                        <div class="form-group md:col-span-2">
                            <label class="form-label">Keluhan Pelanggan *</label>
                            <textarea
                                v-model="form.detail.complaint"
                                class="form-input"
                                rows="3"
                                required
                                placeholder="Deskripsikan kerusakan / masalah yang dialami pelanggan..."
                            />
                            <p v-if="errors['detail.complaint']" class="form-error">
                                {{ errors['detail.complaint'][0] }}
                            </p>
                        </div>

                        <!-- Only show on edit -->
                        <template v-if="isEdit">
                            <div class="form-group md:col-span-2">
                                <label class="form-label">Hasil Diagnosa</label>
                                <textarea
                                    v-model="form.detail.diagnosis"
                                    class="form-input"
                                    rows="3"
                                    placeholder="Hasil diagnosa teknisi..."
                                />
                            </div>
                            <div class="form-group md:col-span-2">
                                <label class="form-label">Catatan Perbaikan</label>
                                <textarea
                                    v-model="form.detail.repair_notes"
                                    class="form-input"
                                    rows="3"
                                    placeholder="Apa yang sudah dilakukan..."
                                />
                            </div>
                            <div class="form-group">
                                <label class="form-label">Biaya Aktual</label>
                                <input
                                    v-model="form.detail.actual_cost"
                                    type="number"
                                    class="form-input"
                                    min="0"
                                    step="1000"
                                    placeholder="0"
                                />
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex gap-3 pt-2 border-t border-gray-100">
                    <button type="button" class="btn-secondary" @click="$router.back()">
                        Batal
                    </button>
                    <button type="submit" class="btn-primary" :disabled="saving">
                        <ArrowPathIcon v-if="saving" class="w-4 h-4 animate-spin" />
                        {{
                            saving ? 'Menyimpan...' : isEdit ? 'Update Transaksi' : 'Buat Transaksi'
                        }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '@/api/axios'
import { ArrowPathIcon } from '@heroicons/vue/24/outline'

const props = defineProps({ transactionId: { type: [String, Number], default: null } })

const router = useRouter()
const saving = ref(false)
const errors = ref({})
const products = ref([])
const technicians = ref([])

const isEdit = computed(() => !!props.transactionId)

const form = reactive({
    customer_name: '',
    customer_phone: '',
    customer_email: '',
    technician_id: '',
    received_date: new Date().toISOString().slice(0, 10),
    order_number: '',
    detail: {
        product_id: '',
        complaint: '',
        diagnosis: '',
        repair_notes: '',
        estimated_cost: 0,
        actual_cost: 0,
    },
})

async function load() {
    const [prodRes, techRes] = await Promise.all([
        api.get('/products/all'),
        api.get('/technicians'),
    ])
    products.value = prodRes.data.data
    technicians.value = techRes.data.data

    if (isEdit.value) {
        const { data } = await api.get(`/transactions/${props.transactionId}`)
        const t = data.data
        Object.assign(form, {
            customer_name: t.customer_name,
            customer_phone: t.customer_phone,
            customer_email: t.customer_email || '',
            technician_id: t.technician_id || '',
            received_date: t.received_date ? t.received_date.split('T')[0] : '',
            order_number: t.order_number,
        })
        if (t.detail) {
            Object.assign(form.detail, {
                product_id: t.detail.product_id,
                complaint: t.detail.complaint,
                diagnosis: t.detail.diagnosis || '',
                repair_notes: t.detail.repair_notes || '',
                estimated_cost: t.detail.estimated_cost,
                actual_cost: t.detail.actual_cost,
            })
        }
    }
}

async function save() {
    saving.value = true
    errors.value = {}
    try {
        const payload = { ...form, detail: { ...form.detail } }
        if (!payload.technician_id) payload.technician_id = null

        if (isEdit.value) {
            await api.put(`/transactions/${props.transactionId}`, payload)
        } else {
            await api.post('/transactions', payload)
        }
        router.push('/admin/transactions')
    } catch (e) {
        if (e.response?.status === 422) errors.value = e.response.data.errors
    } finally {
        saving.value = false
    }
}

onMounted(load)
</script>
