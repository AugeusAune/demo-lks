<template>
    <div class="space-y-4">
        <!-- Toolbar -->
        <div class="flex flex-wrap items-center gap-3">
            <input
                v-model="filters.search"
                type="text"
                class="form-input w-64"
                placeholder="Cari nama / email..."
                @input="fetchDebounced"
            />
            <select v-model="filters.role" class="form-select w-40" @change="fetch">
                <option value="">Semua Role</option>
                <option value="admin">Admin</option>
                <option value="technician">Teknisi</option>
                <option value="cashier">Kasir</option>
            </select>
            <button class="btn-primary ml-auto" @click="openCreate">
                <PlusIcon class="w-4 h-4" /> Tambah User
            </button>
        </div>

        <!-- Table -->
        <div class="card">
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Telepon</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loading">
                            <td colspan="7" class="text-center py-10 text-gray-400">
                                <ArrowPathIcon class="w-5 h-5 animate-spin mx-auto mb-2" />
                                Memuat...
                            </td>
                        </tr>
                        <tr v-else-if="!users.data?.length">
                            <td colspan="7" class="text-center py-10 text-gray-400">
                                Tidak ada data
                            </td>
                        </tr>
                        <tr v-for="u in users.data" :key="u.id">
                            <td class="text-gray-400 text-xs">{{ u.id }}</td>
                            <td class="font-medium">{{ u.name }}</td>
                            <td class="text-gray-500">{{ u.email }}</td>
                            <td>
                                <span :class="roleClass(u.role)" class="badge capitalize">{{
                                    u.role
                                }}</span>
                            </td>
                            <td class="text-gray-500">{{ u.phone || '-' }}</td>
                            <td>
                                <span
                                    :class="u.is_active ? 'badge-green' : 'badge-red'"
                                    class="badge"
                                >
                                    {{ u.is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td>
                                <div class="flex gap-2">
                                    <button class="btn-outline btn-sm" @click="openEdit(u)">
                                        <PencilIcon class="w-3.5 h-3.5" />
                                    </button>
                                    <button class="btn-danger btn-sm" @click="confirmDelete(u)">
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
                    :meta="users.meta"
                    @change="
                        (p) => {
                            filters.page = p
                            fetch()
                        }
                    "
                />
            </div>
        </div>

        <!-- Modal Form -->
        <Teleport to="body">
            <Transition name="fade">
                <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
                    <div class="modal">
                        <div class="modal-header">
                            <h3 class="font-semibold text-gray-800">
                                {{ editTarget ? 'Edit User' : 'Tambah User' }}
                            </h3>
                            <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                                <XMarkIcon class="w-5 h-5" />
                            </button>
                        </div>
                        <form @submit.prevent="save">
                            <div class="modal-body space-y-4">
                                <div class="form-group">
                                    <label class="form-label">Nama Lengkap *</label>
                                    <input v-model="form.name" class="form-input" required />
                                    <p v-if="errors.name" class="form-error">
                                        {{ errors.name[0] }}
                                    </p>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Email *</label>
                                    <input
                                        v-model="form.email"
                                        type="email"
                                        class="form-input"
                                        required
                                    />
                                    <p v-if="errors.email" class="form-error">
                                        {{ errors.email[0] }}
                                    </p>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">{{
                                        editTarget
                                            ? 'Password (kosongkan jika tidak diubah)'
                                            : 'Password *'
                                    }}</label>
                                    <input
                                        v-model="form.password"
                                        type="password"
                                        class="form-input"
                                        :required="!editTarget"
                                        minlength="6"
                                    />
                                    <p v-if="errors.password" class="form-error">
                                        {{ errors.password[0] }}
                                    </p>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="form-group">
                                        <label class="form-label">Role *</label>
                                        <select v-model="form.role" class="form-select" required>
                                            <option value="admin">Admin</option>
                                            <option value="technician">Teknisi</option>
                                            <option value="cashier">Kasir</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">No. Telepon</label>
                                        <input
                                            v-model="form.phone"
                                            class="form-input"
                                            placeholder="08..."
                                        />
                                    </div>
                                </div>
                                <div v-if="editTarget" class="form-group">
                                    <label class="form-label">Status</label>
                                    <select v-model="form.is_active" class="form-select">
                                        <option :value="true">Aktif</option>
                                        <option :value="false">Nonaktif</option>
                                    </select>
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

        <!-- Confirm Delete -->
        <ConfirmModal
            :show="showConfirm"
            title="Hapus User"
            :message="`Hapus user '${deleteTarget?.name}'? Tindakan ini tidak bisa dibatalkan.`"
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

const users = ref({})
const loading = ref(false)
const showModal = ref(false)
const saving = ref(false)
const showConfirm = ref(false)
const deleting = ref(false)
const editTarget = ref(null)
const deleteTarget = ref(null)
const toast = ref(null)
const errors = ref({})

const filters = reactive({ search: '', role: '', page: 1 })

const form = reactive({
    name: '',
    email: '',
    password: '',
    role: 'technician',
    phone: '',
    is_active: true,
})

let debounceTimer = null
function fetchDebounced() {
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(fetch, 350)
}

async function fetch() {
    loading.value = true
    try {
        const { data } = await api.get('/users', { params: { ...filters } })
        users.value = data.data
    } finally {
        loading.value = false
    }
}

function openCreate() {
    editTarget.value = null
    Object.assign(form, {
        name: '',
        email: '',
        password: '',
        role: 'technician',
        phone: '',
        is_active: true,
    })
    errors.value = {}
    showModal.value = true
}

function openEdit(u) {
    editTarget.value = u
    Object.assign(form, {
        name: u.name,
        email: u.email,
        password: '',
        role: u.role,
        phone: u.phone || '',
        is_active: u.is_active,
    })
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
            await api.put(`/users/${editTarget.value.id}`, form)
            toast.value.add('User berhasil diupdate', 'success')
        } else {
            await api.post('/users', form)
            toast.value.add('User berhasil ditambahkan', 'success')
        }
        closeModal()
        fetch()
    } catch (e) {
        if (e.response?.status === 422) errors.value = e.response.data.errors
        else toast.value.add(e.response?.data?.message || 'Gagal menyimpan', 'error')
    } finally {
        saving.value = false
    }
}

function confirmDelete(u) {
    deleteTarget.value = u
    showConfirm.value = true
}

async function doDelete() {
    deleting.value = true
    try {
        await api.delete(`/users/${deleteTarget.value.id}`)
        toast.value.add('User berhasil dihapus', 'success')
        showConfirm.value = false
        fetch()
    } catch (e) {
        toast.value.add(e.response?.data?.message || 'Gagal menghapus', 'error')
    } finally {
        deleting.value = false
    }
}

function roleClass(role) {
    return (
        { admin: 'badge-blue', technician: 'badge-green', cashier: 'badge-yellow' }[role] ||
        'badge-gray'
    )
}

onMounted(fetch)
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
