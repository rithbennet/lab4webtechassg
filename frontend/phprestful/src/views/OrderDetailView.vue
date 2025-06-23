<template>
  <div class="max-w-4xl mx-auto">
    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md mb-6">
      {{ error }}
      <div class="mt-4">
        <router-link to="/orders" class="text-blue-600 hover:text-blue-800">
          ← Back to Orders
        </router-link>
      </div>
    </div>

    <!-- Order Details -->
    <div v-else-if="order" class="bg-white shadow rounded-lg overflow-hidden">
      <!-- Navigation -->
      <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
        <router-link to="/orders" class="text-blue-600 hover:text-blue-800 flex items-center space-x-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          <span>Back to Orders</span>
        </router-link>
      </div>

      <div class="p-6">
        <!-- Order Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Order #{{ order.id }}</h1>
            <p v-if="order.createdAt" class="text-gray-600 mt-1">
              Placed on {{ new Date(order.createdAt).toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
              }) }}
            </p>
          </div>
          
          <div class="mt-4 md:mt-0">
            <span 
              class="inline-flex px-4 py-2 text-sm font-semibold rounded-full"
              :class="getStatusColor(order.status)"
            >
              {{ order.status || 'pending' }}
            </span>
          </div>
        </div>

        <!-- Order Details Grid -->
        <div class="grid md:grid-cols-2 gap-8">
          <!-- Product Information -->
          <div>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Product Details</h2>
            
            <div class="bg-gray-50 rounded-lg p-4 space-y-3">
              <div v-if="order.productName" class="flex justify-between">
                <span class="font-medium text-gray-700">Product:</span>
                <span class="text-gray-900">{{ order.productName }}</span>
              </div>
              
              <div v-if="order.quantity" class="flex justify-between">
                <span class="font-medium text-gray-700">Quantity:</span>
                <span class="text-gray-900">{{ order.quantity }}</span>
              </div>
              
              <div v-if="order.price" class="flex justify-between">
                <span class="font-medium text-gray-700">Unit Price:</span>
                <span class="text-gray-900">${{ parseFloat(order.price).toFixed(2) }}</span>
              </div>
              
              <div v-if="order.totalAmount" class="flex justify-between border-t border-gray-300 pt-3">
                <span class="font-semibold text-gray-900">Total Amount:</span>
                <span class="font-bold text-lg text-blue-600">${{ parseFloat(order.totalAmount).toFixed(2) }}</span>
              </div>
            </div>
          </div>

          <!-- Order Information -->
          <div>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Order Information</h2>
            
            <div class="space-y-4">
              <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="font-medium text-gray-700 mb-2">Order Status</h3>
                <div class="space-y-2">
                  <div class="flex items-center space-x-3">
                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    <span class="text-sm text-gray-600">Order placed</span>
                  </div>
                  <div class="flex items-center space-x-3">
                    <div 
                      class="w-3 h-3 rounded-full"
                      :class="['processing', 'shipped', 'completed'].includes(order.status?.toLowerCase()) ? 'bg-green-500' : 'bg-gray-300'"
                    ></div>
                    <span class="text-sm text-gray-600">Processing</span>
                  </div>
                  <div class="flex items-center space-x-3">
                    <div 
                      class="w-3 h-3 rounded-full"
                      :class="['shipped', 'completed'].includes(order.status?.toLowerCase()) ? 'bg-green-500' : 'bg-gray-300'"
                    ></div>
                    <span class="text-sm text-gray-600">Shipped</span>
                  </div>
                  <div class="flex items-center space-x-3">
                    <div 
                      class="w-3 h-3 rounded-full"
                      :class="order.status?.toLowerCase() === 'completed' ? 'bg-green-500' : 'bg-gray-300'"
                    ></div>
                    <span class="text-sm text-gray-600">Delivered</span>
                  </div>
                </div>
              </div>

              <div v-if="order.productId" class="bg-gray-50 rounded-lg p-4">
                <h3 class="font-medium text-gray-700 mb-2">Additional Details</h3>
                <div class="space-y-2 text-sm text-gray-600">
                  <p><span class="font-medium">Product ID:</span> {{ order.productId }}</p>
                  <p><span class="font-medium">Order ID:</span> {{ order.id }}</p>
                  <p v-if="order.userId"><span class="font-medium">Customer ID:</span> {{ order.userId }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="mt-8 pt-6 border-t border-gray-200">
          <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
            <router-link 
              v-if="order.productId"
              :to="`/products/${order.productId}`"
              class="bg-blue-600 text-white text-center px-6 py-3 rounded-md hover:bg-blue-700 transition duration-200 flex items-center justify-center space-x-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              <span>View Product</span>
            </router-link>
            
            <button 
              v-if="order.status?.toLowerCase() === 'pending'"
              @click="cancelOrder"
              class="border border-red-500 text-red-500 px-6 py-3 rounded-md hover:bg-red-500 hover:text-white transition duration-200 flex items-center justify-center space-x-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
              <span>Cancel Order</span>
            </button>

            <router-link 
              to="/products"
              class="border border-blue-600 text-blue-600 text-center px-6 py-3 rounded-md hover:bg-blue-600 hover:text-white transition duration-200 flex items-center justify-center space-x-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5H17m0 0a2 2 0 002 2m0 0a2 2 0 002-2m-6 0a2 2 0 002 2m0 0a2 2 0 002-2" />
              </svg>
              <span>Continue Shopping</span>
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuth } from '../composables/useAuth'
import { orderApi } from '../api'

const route = useRoute()
const router = useRouter()
const { loggedInUser } = useAuth()

const order = ref(null)
const loading = ref(true)
const error = ref('')

const fetchOrder = async () => {
  try {
    loading.value = true
    error.value = ''
    const fetchedOrder = await orderApi.getById(route.params.id)
    
    // Check if the order belongs to the current user (basic security)
    if (loggedInUser.value && fetchedOrder.userId !== loggedInUser.value.id) {
      error.value = 'Order not found or access denied.'
      return
    }
    
    order.value = fetchedOrder
  } catch (err) {
    error.value = 'Order not found or failed to load.'
    console.error('Failed to fetch order:', err)
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

const cancelOrder = async () => {
  if (!confirm('Are you sure you want to cancel this order?')) {
    return
  }

  try {
    const updatedOrder = {
      ...order.value,
      status: 'cancelled',
      updatedAt: new Date().toISOString()
    }
    
    await orderApi.update(order.value.id, updatedOrder)
    order.value = updatedOrder
  } catch (err) {
    error.value = 'Failed to cancel order. Please try again.'
    console.error('Failed to cancel order:', err)
  }
}

onMounted(() => {
  fetchOrder()
})
</script>
