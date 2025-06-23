import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import { useAuth } from '../composables/useAuth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/LoginView.vue'),
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('../views/RegisterView.vue'),
    },
    {
      path: '/profile',
      name: 'profile',
      component: () => import('../views/ProfileView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/products',
      name: 'products',
      component: () => import('../views/ProductListView.vue'),
    },
    {
      path: '/products/new',
      name: 'product-new',
      component: () => import('../views/ProductFormView.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/products/:id',
      name: 'product-detail',
      component: () => import('../views/ProductDetailView.vue'),
    },
    {
      path: '/products/:id/edit',
      name: 'product-edit',
      component: () => import('../views/ProductFormView.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/orders',
      name: 'orders',
      component: () => import('../views/OrderListView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/orders/:id',
      name: 'order-detail',
      component: () => import('../views/OrderDetailView.vue'),
      meta: { requiresAuth: true }
    },
  ],
})

// Navigation guards
router.beforeEach((to, from, next) => {
  const { loggedInUser } = useAuth()

  // Check if route requires authentication
  if (to.meta.requiresAuth && !loggedInUser.value) {
    next('/login')
    return
  }

  // Check if route requires admin role
  if (to.meta.requiresAdmin && (!loggedInUser.value || loggedInUser.value.role !== 'admin')) {
    next('/products') // Redirect to products if not admin
    return
  }

  next()
})

export default router
