<template>
  <div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">My Orders</h1>
      <p class="text-gray-600 mt-1">Track and manage your orders</p>
    </div>

    <!-- Login Check -->
    <div v-if="!loggedInUser" class="text-center py-12">
      <div class="bg-white shadow rounded-lg p-8">
        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
        </svg>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Login Required</h2>
        <p class="text-gray-600 mb-6">Please log in to view your orders</p>
        <div class="space-x-4">
          <router-link 
            to="/login" 
            class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition duration-200"
          >
            Login
          </router-link>
          <router-link 
            to="/register" 
            class="border border-blue-600 text-blue-600 px-6 py-2 rounded-md hover:bg-blue-600 hover:text-white transition duration-200"
          >
            Register
          </router-link>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-else-if="loading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md mb-6">
      {{ error }}
    </div>

    <!-- Orders List -->
    <div v-else-if="userOrders.length > 0" class="space-y-6">
      <div 
        v-for="order in userOrders" 
        :key="order.id"
        class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-300"
      >
        <div class="p-6">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <!-- Order Info -->
            <div class="flex-1">
              <div class="flex items-center space-x-4 mb-2">
                <h3 class="text-lg font-semibold text-gray-900">
                  Order #{{ order.id }}
                </h3>
                <span 
                  class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                  :class="getStatusColor(order.status)"
                >
                  {{ order.status || 'pending' }}
                </span>
              </div>
              
              <div class="text-gray-600 space-y-1">
                <p v-if="order.productName">
                  <span class="font-medium">Product:</span> {{ order.productName }}
                </p>
                <p v-if="order.quantity">
                  <span class="font-medium">Quantity:</span> {{ order.quantity }}
                </p>
                <p v-if="order.totalAmount">
                  <span class="font-medium">Total:</span> ${{ parseFloat(order.totalAmount).toFixed(2) }}
                </p>
                <p v-if="order.createdAt">
                  <span class="font-medium">Date:</span> {{ new Date(order.createdAt).toLocaleDateString() }}
                </p>
              </div>
            </div>

            <!-- Actions -->
            <div class="mt-4 md:mt-0 md:ml-4">
              <router-link 
                :to="`/orders/${order.id}`"
                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-200 flex items-center space-x-2"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <span>View Details</span>
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-12">
      <div class="bg-white shadow rounded-lg p-8">
        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
        </svg>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">No Orders Found</h2>
        <p class="text-gray-600 mb-6">You haven't placed any orders yet.</p>
        <router-link 
          to="/products"
          class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition duration-200"
        >
          Browse Products
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuth } from '../composables/useAuth'
import { orderApi } from '../api'

const { loggedInUser } = useAuth()

const orders = ref([])
const loading = ref(true)
const error = ref('')

// Filter orders for current user
const userOrders = computed(() => {
  if (!loggedInUser.value) return []
  return orders.value.filter(order => order.userId === loggedInUser.value.id)
})

const fetchOrders = async () => {
  if (!loggedInUser.value) {
    loading.value = false
    return
  }

  try {
    loading.value = true
    error.value = ''
    const allOrders = await orderApi.getAll()
    orders.value = allOrders.filter(order => order.userId === loggedInUser.value.id)
  } catch (err) {
    error.value = 'Failed to load orders. Please try again.'
    console.error('Failed to fetch orders:', err)
  } finally {
    loading.value = false
  }
}

const getStatusColor = (status) => {
  switch (status?.toLowerCase()) {
    case 'completed':
      return 'bg-green-100 text-green-800'
    case 'processing':
      return 'bg-blue-100 text-blue-800'
    case 'shipped':
      return 'bg-purple-100 text-purple-800'
    case 'cancelled':
      return 'bg-red-100 text-red-800'
    default:
      return 'bg-yellow-100 text-yellow-800'
  }
}

onMounted(() => {
  fetchOrders()
})
</script>
