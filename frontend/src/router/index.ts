import { createRouter, createWebHistory } from 'vue-router'

// Public
import TrackingPage from '@/views/public/TrackingPage.vue'
import LoginPage from '@/views/public/LoginPage.vue'

// Admin Views
import AdminDashboard from '@/views/admin/Dashboard.vue'
import UserIndex from '@/views/admin/users/Index.vue'
import ProductIndex from '@/views/admin/products/Index.vue'
import TransactionIndex from '@/views/admin/transactions/Index.vue'
import TransactionCreate from '@/views/admin/transactions/Create.vue'
import TransactionEdit from '@/views/admin/transactions/Edit.vue'
import TransactionDetail from '@/views/admin/transactions/Detail.vue'

// Technician Views
import TechDashboard from '@/views/technician/Dashboard.vue'
import TechOrders from '@/views/technician/Orders.vue'
import TechOrderDetail from '@/views/technician/OrderDetail.vue'
import { useAuthStore } from '@/stores/auth'

// layout
import AdminLayout from '@/layouts/AdminLayout.vue'
import TechnicianLayout from '@/layouts/TechnicianLayout.vue'

const routes = [
    // Public
    { path: '/', name: 'home', component: TrackingPage },
    { path: '/tracking', name: 'tracking', component: TrackingPage },
    { path: '/login', name: 'login', component: LoginPage, meta: { guest: true } },

    // Admin
    {
        path: '/admin',
        component: AdminLayout,
        meta: { requiresAuth: true, roles: ['admin', 'cashier'] },
        children: [
            { path: '', name: 'admin.dashboard', component: AdminDashboard },
            {
                path: 'users',
                name: 'admin.users',
                component: UserIndex,
                meta: { roles: ['admin'] },
            },
            { path: 'products', name: 'admin.products', component: ProductIndex },
            { path: 'transactions', name: 'admin.transactions', component: TransactionIndex },
            {
                path: 'transactions/create',
                name: 'admin.transactions.create',
                component: TransactionCreate,
                meta: { roles: ['admin', 'cashier'] },
            },
            {
                path: 'transactions/:id/edit',
                name: 'admin.transactions.edit',
                component: TransactionEdit,
                meta: { roles: ['admin', 'cashier'] },
            },
            {
                path: 'transactions/:id',
                name: 'admin.transactions.detail',
                component: TransactionDetail,
            },
        ],
    },

    // Technician
    {
        path: '/technician',
        component: TechnicianLayout,
        meta: { requiresAuth: true, roles: ['technician'] },
        children: [
            { path: '', name: 'tech.dashboard', component: TechDashboard },
            { path: 'orders', name: 'tech.orders', component: TechOrders },
            { path: 'orders/:id', name: 'tech.orders.detail', component: TechOrderDetail },
        ],
    },

    // 404
    { path: '/:pathMatch(.*)*', redirect: '/' },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior: () => ({ top: 0 }),
})

router.beforeEach((to, from, next) => {
    const auth = useAuthStore()

    if (to.meta.guest && auth.isLoggedIn) {
        return next(auth.isAdmin ? '/admin' : '/technician')
    }

    if (to.meta.requiresAuth && !auth.isLoggedIn) {
        return next({ name: 'login', query: { redirect: to.fullPath } })
    }

    if (to.meta.roles && auth.user && !to.meta.roles!.includes(auth.user.role)) {
        return next(auth.isAdmin ? '/admin' : '/technician')
    }

    next()
})

export default router
