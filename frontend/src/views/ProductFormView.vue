<template>
  <div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
      <router-link to="/products" class="text-blue-600 hover:text-blue-800 flex items-center space-x-2 mb-4">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        <span>Back to Products</span>
      </router-link>
      
      <h1 class="text-3xl font-bold text-gray-900">
        {{ isEditMode ? 'Edit Product' : 'Add New Product' }}
      </h1>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <!-- Form -->
    <div v-else class="bg-white shadow rounded-lg p-6">
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Product Name -->
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
            Product Name *
          </label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            placeholder="Enter product name"
          />
        </div>

        <!-- Description -->
        <div>
          <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
            Description
          </label>
          <textarea
            id="description"
            v-model="form.description"
            rows="4"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            placeholder="Enter product description"
          ></textarea>
        </div>

        <!-- Price -->
        <div>
          <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
            Price *
          </label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <span class="text-gray-500 sm:text-sm">$</span>
            </div>
            <input
              id="price"
              v-model="form.price"
              type="number"
              step="0.01"
              min="0"
              required
              class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="0.00"
            />
          </div>
        </div>

        <!-- Category -->
        <div>
          <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
            Category
          </label>
          <input
            id="category"
            v-model="form.category"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            placeholder="Enter product category"
          />
        </div>

        <!-- Brand -->
        <div>
          <label for="brand" class="block text-sm font-medium text-gray-700 mb-2">
            Brand
          </label>
          <input
            id="brand"
            v-model="form.brand"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            placeholder="Enter brand name"
          />
        </div>

        <!-- Stock -->
        <div>
          <label for="stock" class="block text-sm font-medium text-gray-700 mb-2">
            Stock Quantity
          </label>
          <input
            id="stock"
            v-model="form.stock"
            type="number"
            min="0"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            placeholder="Enter stock quantity"
          />
        </div>

        <!-- Error Message -->
        <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
          {{ error }}
        </div>

        <!-- Success Message -->
        <div v-if="success" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md">
          {{ success }}
        </div>

        <!-- Form Actions -->
        <div class="flex space-x-4">
          <button
            type="submit"
            :disabled="submitting"
            class="flex-1 bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center space-x-2"
          >
            <svg v-if="submitting" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ submitting ? 'Saving...' : (isEditMode ? 'Update Product' : 'Create Product') }}</span>
          </button>
          
          <router-link 
            to="/products"
            class="flex-1 border border-gray-300 text-gray-700 text-center px-6 py-3 rounded-md hover:bg-gray-50 transition duration-200"
          >
            Cancel
          </router-link>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { productApi } from '../api'

const route = useRoute()
const router = useRouter()

const isEditMode = computed(() => route.name === 'product-edit')
const productId = computed(() => route.params.id)

const form = ref({
  name: '',
  description: '',
  price: '',
  category: '',
  brand: '',
  stock: ''
})

const loading = ref(false)
const submitting = ref(false)
const error = ref('')
const success = ref('')

const fetchProduct = async () => {
  if (!isEditMode.value) return

  try {
    loading.value = true
    error.value = ''
    const product = await productApi.getById(productId.value)
    
    // Populate form with existing data
    form.value = {
      name: product.name || '',
      description: product.description || '',
      price: product.price || '',
      category: product.category || '',
      brand: product.brand || '',
      stock: product.stock || ''
    }
  } catch (err) {
    error.value = 'Failed to load product. Please try again.'
    console.error('Failed to fetch product:', err)
  } finally {
    loading.value = false
  }
}

const handleSubmit = async () => {
  error.value = ''
  success.value = ''
  submitting.value = true

  try {
    const productData = {
      name: form.value.name,
      description: form.value.description,
      price: parseFloat(form.value.price),
      category: form.value.category,
      brand: form.value.brand,
      stock: form.value.stock ? parseInt(form.value.stock) : 0,
      updatedAt: new Date().toISOString()
    }

    if (isEditMode.value) {
      await productApi.update(productId.value, productData)
      success.value = 'Product updated successfully!'
    } else {
      productData.createdAt = new Date().toISOString()
      await productApi.create(productData)
      success.value = 'Product created successfully!'
    }

    // Redirect after success
    setTimeout(() => {
      router.push('/products')
    }, 1500)

  } catch (err) {
    error.value = isEditMode.value ? 'Failed to update product. Please try again.' : 'Failed to create product. Please try again.'
    console.error('Failed to save product:', err)
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  fetchProduct()
})
</script>
