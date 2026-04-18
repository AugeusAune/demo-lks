<template>
    <div class="space-y-4">
        <div class="flex flex-wrap gap-3">
            <input
                v-model="filters.search"
                type="text"
                class="form-input w-64"
                placeholder="Cari produk..."
                @input="fetchDebounced"
            />
            <select v-model="filters.category" class="form-select w-48" @change="fetch">
                <option value="">Semua Kategori</option>
                <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
            </select>
            <button class="btn-primary ml-auto" @click="openCreate">
                <PlusIcon class="w-4 h-4" /> Tambah Produk
            </button>
        </div>

        <div class="card">
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Deskripsi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loading">
                            <td colspan="5" class="text-center py-10 text-gray-400">
                                <ArrowPathIcon class="w-5 h-5 animate-spin mx-auto mb-2" />Memuat...
                            </td>
                        </tr>
                        <tr v-else-if="!products.data?.length">
                            <td colspan="5" class="text-center py-10 text-gray-400">
                                Tidak ada produk
                            </td>
                        </tr>
                        <tr v-for="p in products.data" :key="p.id">
                            <td class="text-gray-400 text-xs">{{ p.id }}</td>
                            <td class="font-medium">{{ p.name }}</td>
                            <td>
                                <span class="badge badge-blue">{{ p.category }}</span>
                            </td>
                            <td class="text-gray-500 text-sm max-w-xs truncate">
                                {{ p.description || '-' }}
                            </td>
                            <td>
                                <div class="flex gap-2">
                                    <button class="btn-outline btn-sm" @click="openEdit(p)">
                                        <PencilIcon class="w-3.5 h-3.5" />
                                    </button>
                                    <button class="btn-danger btn-sm" @click="confirmDelete(p)">
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
                    :meta="products.meta"
                    @change="
                        (p) => {
                            filters.page = p
                            fetch()
                        }
                    "
                />
            </div>
        </div>

        <!-- Modal -->
        <Teleport to="body">
            <Transition name="fade">
                <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
                    <div class="modal">
                        <div class="modal-header">
                            <h3 class="font-semibold text-gray-800">
                                {{ editTarget ? 'Edit Produk' : 'Tambah Produk' }}
                            </h3>
                            <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                                <XMarkIcon class="w-5 h-5" />
                            </button>
                        </div>
                        <form @submit.prevent="save">
                            <div class="modal-body space-y-4">
                                <div class="form-group">
                                    <label class="form-label">Nama Produk *</label>
                                    <input v-model="form.name" class="form-input" required />
                                    <p v-if="errors.name" class="form-error">
                                        {{ errors.name[0] }}
                                    </p>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Kategori *</label>
                                    <input
                                        v-model="form.category"
                                        class="form-input"
                                        list="cat-list"
                                        required
                                        placeholder="Laptop, Handphone, Printer..."
                                    />
                                    <datalist id="cat-list">
                                        <option v-for="c in categories" :key="c" :value="c" />
                                    </datalist>
                                    <p v-if="errors.category" class="form-error">
                                        {{ errors.category[0] }}
                                    </p>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Deskripsi</label>
                                    <textarea
                                        v-model="form.description"
                                        class="form-input"
                                        rows="3"
                                    />
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn-secondary" @click="closeModal">
                                    Batal
                                </button>
                                <button type="submit" class="btn-primary" :disabled="saving">
                                    <ArrowPathIcon v-if="saving" class="w-4 h-4 animate-spin" />
                                    {{ saving ? 'Menyimpan...' : 'Simpan' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <ConfirmModal
            :show="showConfirm"
            title="Hapus Produk"
            :message="`Hapus produk '${deleteTarget?.name}'?`"
            :loading="deleting"
            @confirm="doDelete"
            @cancel="showConfirm = false"
        />

        <Toast ref="toast" />
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '@/api/axios'
import Pagination from '@/components/Pagination.vue'
import ConfirmModal from '@/components/ConfirmModal.vue'
import Toast from '@/components/Toast.vue'
import {
    PlusIcon,
    PencilIcon,
    TrashIcon,
    ArrowPathIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline'

const products = ref({})
const categories = ref([])
const loading = ref(false)
const showModal = ref(false)
const saving = ref(false)
const showConfirm = ref(false)
const deleting = ref(false)
const editTarget = ref(null)
const deleteTarget = ref(null)
const toast = ref(null)
const errors = ref({})

const filters = reactive({ search: '', category: '', page: 1 })
const form = reactive({ name: '', category: '', description: '' })

let debounceTimer = null
function fetchDebounced() {
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(fetch, 350)
}

async function fetch() {
    loading.value = true
    try {
        const { data } = await api.get('/products', { params: filters })
        products.value = data.data
    } finally {
        loading.value = false
    }
}

async function fetchCategories() {
    const { data } = await api.get('/products/categories')
    categories.value = data.data
}

function openCreate() {
    editTarget.value = null
    Object.assign(form, { name: '', category: '', description: '' })
    errors.value = {}
    showModal.value = true
}

function openEdit(p) {
    editTarget.value = p
    Object.assign(form, { name: p.name, category: p.category, description: p.description || '' })
    errors.value = {}
    showModal.value = true
}

function closeModal() {
    showModal.value = false
}

async function save() {
    saving.value = true
    errors.value = {}
    try {
        if (editTarget.value) {
            await api.put(`/products/${editTarget.value.id}`, form)
            toast.value.add('Produk diupdate', 'success')
        } else {
            await api.post('/products', form)
            toast.value.add('Produk ditambahkan', 'success')
        }
        closeModal()
        fetch()
        fetchCategories()
    } catch (e) {
        if (e.response?.status === 422) errors.value = e.response.data.errors
        else toast.value.add(e.response?.data?.message || 'Gagal menyimpan', 'error')
    } finally {
        saving.value = false
    }
}

function confirmDelete(p) {
    deleteTarget.value = p
    showConfirm.value = true
}

async function doDelete() {
    deleting.value = true
    try {
        await api.delete(`/products/${deleteTarget.value.id}`)
        toast.value.add('Produk dihapus', 'success')
        showConfirm.value = false
        fetch()
        fetchCategories()
    } catch (e) {
        toast.value.add(e.response?.data?.message || 'Gagal menghapus', 'error')
    } finally {
        deleting.value = false
    }
}

onMounted(() => {
    fetch()
    fetchCategories()
})
</script>
<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
