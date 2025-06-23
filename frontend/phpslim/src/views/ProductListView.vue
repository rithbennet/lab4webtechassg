<template>
  <div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Products</h1>
        <p class="text-gray-600 mt-1">Browse our collection of amazing products</p>
      </div>
      <router-link 
        v-if="loggedInUser && loggedInUser.role === 'admin'"
        to="/products/new"
        class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition duration-200 flex items-center space-x-2"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        <span>Add Product</span>
      </router-link>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md mb-6">
      {{ error }}
    </div>

    <!-- Products Grid -->
    <div v-else-if="products.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div 
        v-for="product in products" 
        :key="product.id"
        class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-300"
      >
        <!-- Product Image Placeholder -->
        <div class="h-48 bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center">
          <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
        </div>

        <!-- Product Content -->
        <div class="p-6">
          <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ product.name }}</h3>
          <p v-if="product.description" class="text-gray-600 mb-4 line-clamp-2">{{ product.description }}</p>
          
          <!-- Price -->
          <div class="flex items-center justify-between mb-4">
            <span class="text-2xl font-bold text-blue-600">
              ${{ parseFloat(product.price).toFixed(2) }}
            </span>
            <span v-if="product.stock" class="text-sm text-gray-500">
              Stock: {{ product.stock }}
            </span>
          </div>

          <!-- Action Buttons -->
          <div class="flex space-x-2">
            <router-link 
              :to="`/products/${product.id}`"
              class="flex-1 bg-blue-600 text-white text-center px-4 py-2 rounded-md hover:bg-blue-700 transition duration-200"
            >
              View Details
            </router-link>
            
            <div v-if="loggedInUser && loggedInUser.role === 'admin'" class="flex space-x-2">
              <router-link 
                :to="`/products/${product.id}/edit`"
                class="bg-yellow-500 text-white px-3 py-2 rounded-md hover:bg-yellow-600 transition duration-200"
                title="Edit Product"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
              </router-link>
              
              <button 
                @click="deleteProduct(product)"
                class="bg-red-500 text-white px-3 py-2 rounded-md hover:bg-red-600 transition duration-200"
                title="Delete Product"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-12">
      <div class="bg-white shadow rounded-lg p-8">
        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
        </svg>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">No Products Found</h2>
        <p class="text-gray-600 mb-6">There are no products available at the moment.</p>
        <router-link 
          v-if="loggedInUser && loggedInUser.role === 'admin'"
          to="/products/new"
          class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition duration-200"
        >
          Add First Product
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuth } from '../composables/useAuth'
import { productApi } from '../api'

const { loggedInUser } = useAuth()

const products = ref([])
const loading = ref(true)
const error = ref('')

const fetchProducts = async () => {
  try {
    loading.value = true
    error.value = ''
    products.value = await productApi.getAll()
  } catch (err) {
    error.value = 'Failed to load products. Please try again.'
    console.error('Failed to fetch products:', err)
  } finally {
    loading.value = false
  }
}

const deleteProduct = async (product) => {
  if (!confirm(`Are you sure you want to delete "${product.name}"?`)) {
    return
  }

  try {
    await productApi.delete(product.id)
    // Remove from local list
    products.value = products.value.filter(p => p.id !== product.id)
  } catch (err) {
    error.value = 'Failed to delete product. Please try again.'
    console.error('Failed to delete product:', err)
  }
}

onMounted(() => {
  fetchProducts()
})
</script>
