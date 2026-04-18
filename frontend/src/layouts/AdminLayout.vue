<template>
    <div class="flex h-screen bg-gray-50 overflow-hidden">
        <!-- Mobile overlay backdrop -->
        <div v-if="sidebarOpen" class="fixed inset-0 bg-black/40 z-20 md:hidden backdrop-blur-sm" @click="sidebarOpen = false"></div>

        <!-- Sidebar -->
        <aside
            :class="[
                'flex flex-col bg-white border-r border-gray-100 transition-all duration-300 flex-shrink-0 absolute md:relative z-30 h-full',
                sidebarOpen ? 'w-64 translate-x-0' : '-translate-x-full md:translate-x-0 md:w-16',
            ]"
        >
            <!-- Logo -->
            <div class="flex items-center gap-3 px-4 py-5 border-b border-gray-100 overflow-hidden">
                <div
                    class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center flex-shrink-0"
                >
                    <span class="text-white text-sm font-bold">SC</span>
                </div>
                <span v-if="sidebarOpen" class="font-bold text-gray-800 text-sm whitespace-nowrap"
                    >Service Center</span
                >
            </div>

            <!-- Nav -->
            <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
                <RouterLink
                    v-for="item in navItems"
                    :key="item.name"
                    :to="item.to"
                    :class="isActive(item.to) ? 'sidebar-link-active' : 'sidebar-link-inactive'"
                    :title="item.label"
                >
                    <component :is="item.icon" class="w-5 h-5 flex-shrink-0" />
                    <span v-if="sidebarOpen" class="whitespace-nowrap">{{ item.label }}</span>
                </RouterLink>
            </nav>

            <!-- User info -->
            <div class="border-t border-gray-100 p-3">
                <div
                    v-if="sidebarOpen"
                    class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50"
                >
                    <div
                        class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0"
                    >
                        <span class="text-blue-600 text-xs font-bold">{{ userInitial }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-700 truncate">
                            {{ auth.user?.name }}
                        </p>
                        <p class="text-xs text-gray-400 capitalize">{{ auth.user?.role }}</p>
                    </div>
                </div>
                <button
                    @click="handleLogout"
                    :class="[
                        'w-full flex items-center gap-3 px-2 py-2 rounded-lg text-red-600 hover:bg-red-50 text-sm',
                        !sidebarOpen && 'justify-center',
                    ]"
                >
                    <ArrowRightOnRectangleIcon class="w-5 h-5 flex-shrink-0" />
                    <span v-if="sidebarOpen">Logout</span>
                </button>
            </div>
        </aside>

        <!-- Main -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Topbar -->
            <header
                class="bg-white border-b border-gray-100 px-6 py-3 flex items-center justify-between flex-shrink-0"
            >
                <div class="flex items-center gap-4">
                    <button
                        @click="sidebarOpen = !sidebarOpen"
                        class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-500"
                    >
                        <Bars3Icon class="w-5 h-5" />
                    </button>
                    <h1 class="font-semibold text-gray-800">{{ pageTitle }}</h1>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-500">{{ auth.user?.name }}</span>
                    <span class="badge badge-blue capitalize">{{ auth.user?.role }}</span>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-4 md:p-6 bg-gray-50 relative z-0">
                <RouterView />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { RouterView, RouterLink, useRoute, useRouter } from 'vue-router'
import {
    HomeIcon,
    UsersIcon,
    CubeIcon,
    ClipboardDocumentListIcon,
    Bars3Icon,
    ArrowRightOnRectangleIcon,
} from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()
const sidebarOpen = ref(true)

const userInitial = computed(() => auth.user?.name?.charAt(0).toUpperCase() || 'A')

const allNavItems = [
    {
        name: 'dashboard',
        label: 'Dashboard',
        to: '/admin',
        icon: HomeIcon,
        roles: ['admin', 'cashier'],
    },
    { name: 'users', label: 'Users', to: '/admin/users', icon: UsersIcon, roles: ['admin'] },
    {
        name: 'products',
        label: 'Produk',
        to: '/admin/products',
        icon: CubeIcon,
        roles: ['admin', 'cashier'],
    },
    {
        name: 'transactions',
        label: 'Transaksi',
        to: '/admin/transactions',
        icon: ClipboardDocumentListIcon,
        roles: ['admin', 'cashier'],
    },
]

const navItems = computed(() => allNavItems.filter((i) => i.roles.includes(auth.user?.role)))

const pageTitle = computed(() => {
    const map = {
        'admin.dashboard': 'Dashboard',
        'admin.users': 'Manajemen User',
        'admin.products': 'Manajemen Produk',
        'admin.transactions': 'Manajemen Transaksi',
        'admin.transactions.create': 'Buat Transaksi',
        'admin.transactions.edit': 'Edit Transaksi',
        'admin.transactions.detail': 'Detail Transaksi',
    }
    return map[route.name] || 'Service Center'
})

function isActive(path) {
    if (path === '/admin') return route.path === '/admin'
    return route.path.startsWith(path)
}

async function handleLogout() {
    await auth.logout()
    router.push('/login')
}
</script>
