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
        <router-link to="/products" class="text-blue-600 hover:text-blue-800">
          ← Back to Products
        </router-link>
      </div>
    </div>

    <!-- Product Details -->
    <div v-else-if="product" class="bg-white shadow rounded-lg overflow-hidden">
      <!-- Navigation -->
      <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
        <router-link to="/products" class="text-blue-600 hover:text-blue-800 flex items-center space-x-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          <span>Back to Products</span>
        </router-link>
      </div>

      <div class="p-6">
        <div class="grid md:grid-cols-2 gap-8">
          <!-- Product Image -->
          <div class="h-96 bg-gradient-to-br from-gray-200 to-gray-300 rounded-lg flex items-center justify-center">
            <svg class="w-24 h-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>

          <!-- Product Information -->
          <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ product.name }}</h1>
            
            <div class="mb-6">
              <span class="text-4xl font-bold text-blue-600">
                ${{ parseFloat(product.price).toFixed(2) }}
              </span>
            </div>

            <div v-if="product.description" class="mb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-2">Description</h3>
              <p class="text-gray-700 leading-relaxed">{{ product.description }}</p>
            </div>

            <!-- Product Details -->
            <div class="space-y-3 mb-6">
              <div v-if="product.category" class="flex items-center">
                <span class="font-medium text-gray-700 w-20">Category:</span>
                <span class="text-gray-600">{{ product.category }}</span>
              </div>
              
              <div v-if="product.stock" class="flex items-center">
                <span class="font-medium text-gray-700 w-20">Stock:</span>
                <span class="text-gray-600">{{ product.stock }} units</span>
              </div>
              
              <div v-if="product.brand" class="flex items-center">
                <span class="font-medium text-gray-700 w-20">Brand:</span>
                <span class="text-gray-600">{{ product.brand }}</span>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-4">
              <!-- Order Button -->
              <button 
                v-if="loggedInUser"
                @click="createOrder"
                :disabled="orderLoading"
                class="w-full bg-green-600 text-white px-6 py-3 rounded-md hover:bg-green-700 transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center space-x-2"
              >
                <svg v-if="orderLoading" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5H17m0 0a2 2 0 002 2m0 0a2 2 0 002-2m-6 0a2 2 0 002 2m0 0a2 2 0 002-2" />
                </svg>
                <span>{{ orderLoading ? 'Placing Order...' : 'Place Order' }}</span>
              </button>

              <!-- Admin Actions -->
              <div v-if="loggedInUser && loggedInUser.role === 'admin'" class="flex space-x-4">
                <router-link 
                  :to="`/products/${product.id}/edit`"
                  class="flex-1 bg-yellow-500 text-white text-center px-6 py-3 rounded-md hover:bg-yellow-600 transition duration-200 flex items-center justify-center space-x-2"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                  <span>Edit Product</span>
                </router-link>
                
                <button 
                  @click="deleteProduct"
                  class="flex-1 bg-red-500 text-white px-6 py-3 rounded-md hover:bg-red-600 transition duration-200 flex items-center justify-center space-x-2"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                  <span>Delete Product</span>
                </button>
              </div>

              <!-- Login prompt for guests -->
              <div v-if="!loggedInUser" class="text-center p-4 bg-gray-50 rounded-md">
                <p class="text-gray-600 mb-3">Please log in to place an order</p>
                <div class="space-x-4">
                  <router-link to="/login" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-200">
                    Login
                  </router-link>
                  <router-link to="/register" class="border border-blue-600 text-blue-600 px-4 py-2 rounded-md hover:bg-blue-600 hover:text-white transition duration-200">
                    Register
                  </router-link>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Success Message -->
    <div v-if="orderSuccess" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-xl max-w-md mx-4">
        <div class="text-center">
          <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-gray-900 mb-2">Order Placed Successfully!</h3>
          <p class="text-gray-600 mb-4">Your order has been created and will be processed soon.</p>
          <div class="space-x-4">
            <button 
              @click="orderSuccess = false"
              class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition duration-200"
            >
              Continue Shopping
            </button>
            <router-link 
              to="/orders"
              class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-200"
            >
              View Orders
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
import { productApi, orderApi } from '../api'

const route = useRoute()
const router = useRouter()
const { loggedInUser } = useAuth()

const product = ref(null)
const loading = ref(true)
const error = ref('')
const orderLoading = ref(false)
const orderSuccess = ref(false)

const fetchProduct = async () => {
  try {
    loading.value = true
    error.value = ''
    product.value = await productApi.getById(route.params.id)
  } catch (err) {
    error.value = 'Product not found or failed to load.'
    console.error('Failed to fetch product:', err)
  } finally {
    loading.value = false
  }
}

const createOrder = async () => {
  if (!loggedInUser.value) return

  try {
    orderLoading.value = true
    
    const orderData = {
      productId: product.value.id,
      userId: loggedInUser.value.id,
      productName: product.value.name,
      price: product.value.price,
      quantity: 1,
      totalAmount: product.value.price,
      status: 'pending',
      createdAt: new Date().toISOString()
    }

    await orderApi.create(orderData)
    orderSuccess.value = true
    
  } catch (err) {
    error.value = 'Failed to place order. Please try again.'
    console.error('Failed to create order:', err)
  } finally {
    orderLoading.value = false
  }
}

const deleteProduct = async () => {
  if (!confirm(`Are you sure you want to delete "${product.value.name}"?`)) {
    return
  }

  try {
    await productApi.delete(product.value.id)
    router.push('/products')
  } catch (err) {
    error.value = 'Failed to delete product. Please try again.'
    console.error('Failed to delete product:', err)
  }
}

onMounted(() => {
  fetchProduct()
})
</script>
