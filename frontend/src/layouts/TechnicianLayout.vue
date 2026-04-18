<template>
    <div class="flex h-screen bg-gray-50 overflow-hidden">
        <!-- Mobile overlay backdrop -->
        <div v-if="sidebarOpen" class="fixed inset-0 bg-black/40 z-20 md:hidden backdrop-blur-sm" @click="sidebarOpen = false"></div>

        <!-- Sidebar -->
        <aside
            :class="[
                'flex flex-col bg-white border-r border-gray-100 transition-all duration-300 flex-shrink-0 absolute md:relative z-30 h-full',
                sidebarOpen ? 'w-64 translate-x-0' : '-translate-x-full md:translate-x-0 md:w-64',
            ]"
        >
            <div class="flex items-center gap-3 px-4 py-5 border-b border-gray-100">
                <div class="w-8 h-8 bg-green-600 rounded-lg flex items-center justify-center">
                    <span class="text-white text-sm font-bold">SC</span>
                </div>
                <div>
                    <span class="font-bold text-gray-800 text-sm">Service Center</span>
                    <p class="text-xs text-green-600 font-medium">Teknisi Panel</p>
                </div>
            </div>

            <nav class="flex-1 px-2 py-4 space-y-1">
                <RouterLink
                    v-for="item in navItems"
                    :key="item.name"
                    :to="item.to"
                    :class="isActive(item.to) ? 'sidebar-link-active' : 'sidebar-link-inactive'"
                >
                    <component :is="item.icon" class="w-5 h-5 flex-shrink-0" />
                    {{ item.label }}
                </RouterLink>
            </nav>

            <div class="border-t border-gray-100 p-3 space-y-1">
                <div class="flex items-center gap-3 px-2 py-2 rounded-lg">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <span class="text-green-600 text-xs font-bold">{{ userInitial }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-700 truncate">
                            {{ auth.user?.name }}
                        </p>
                        <p class="text-xs text-gray-400">Teknisi</p>
                    </div>
                </div>
                <button
                    @click="handleLogout"
                    class="w-full flex items-center gap-3 px-2 py-2 rounded-lg text-red-600 hover:bg-red-50 text-sm"
                >
                    <ArrowRightOnRectangleIcon class="w-5 h-5" />
                    Logout
                </button>
            </div>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header
                class="bg-white border-b border-gray-100 px-6 py-3 flex items-center justify-between flex-shrink-0"
            >
                <div class="flex items-center gap-4">
                    <button
                        @click="sidebarOpen = !sidebarOpen"
                        class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-500 md:hidden"
                    >
                        <Bars3Icon class="w-5 h-5" />
                    </button>
                    <h1 class="font-semibold text-gray-800">{{ pageTitle }}</h1>
                </div>
                <span class="badge badge-green">Teknisi</span>
            </header>
            <main class="flex-1 overflow-y-auto p-4 md:p-6 bg-gray-50 relative z-0">
                <RouterView />
            </main>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { RouterView, RouterLink, useRoute, useRouter } from 'vue-router'
import {
    HomeIcon,
    ClipboardDocumentListIcon,
    ArrowRightOnRectangleIcon,
    Bars3Icon,
} from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()
const sidebarOpen = ref(false)

const userInitial = computed(() => auth.user?.name?.charAt(0).toUpperCase() || 'T')

const navItems = [
    { name: 'dashboard', label: 'Dashboard', to: '/technician', icon: HomeIcon },
    {
        name: 'orders',
        label: 'Order Saya',
        to: '/technician/orders',
        icon: ClipboardDocumentListIcon,
    },
]

const pageTitle = computed(
    () =>
        ({
            'tech.dashboard': 'Dashboard',
            'tech.orders': 'Order Saya',
            'tech.orders.detail': 'Detail Order',
        })[route.name] || 'Panel Teknisi',
)

function isActive(path) {
    if (path === '/technician') return route.path === '/technician'
    return route.path.startsWith(path)
}

async function handleLogout() {
    await auth.logout()
    router.push('/login')
}
</script>
