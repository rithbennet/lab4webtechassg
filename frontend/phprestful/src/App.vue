<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navigation Header -->
    <nav class="bg-white shadow-lg">
      <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between items-center h-16">
          <!-- Logo/Brand -->
          <div class="flex items-center">
            <router-link to="/" class="text-xl font-bold text-blue-600">
              WebTech Store
            </router-link>
          </div>

          <!-- Navigation Links -->
          <div class="hidden md:flex items-center space-x-8">
            <router-link to="/products" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md">
              Products
            </router-link>
            <router-link v-if="isLoggedIn" to="/orders" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md">
              Orders
            </router-link>
            
            <!-- User Menu -->
            <div v-if="isLoggedIn" class="flex items-center space-x-4">
              <router-link to="/profile" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md">
                Profile
              </router-link>
              <button @click="handleLogout" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">
                Logout
              </button>
            </div>
            
            <!-- Auth Links -->
            <div v-else class="flex items-center space-x-4">
              <router-link to="/login" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md">
                Login
              </router-link>
              <router-link to="/register" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                Register
              </router-link>
            </div>
          </div>

          <!-- Mobile menu button -->
          <div class="md:hidden">
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-gray-700 hover:text-blue-600">
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Mobile menu -->
        <div v-if="mobileMenuOpen" class="md:hidden">
          <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
            <router-link to="/products" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md">
              Products
            </router-link>
            <router-link v-if="isLoggedIn" to="/orders" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md">
              Orders
            </router-link>
            
            <div v-if="isLoggedIn" class="space-y-1">
              <router-link to="/profile" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md">
                Profile
              </router-link>
              <button @click="handleLogout" class="block w-full text-left bg-red-500 text-white px-3 py-2 rounded-md hover:bg-red-600">
                Logout
              </button>
            </div>
            
            <div v-else class="space-y-1">
              <router-link to="/login" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md">
                Login
              </router-link>
              <router-link to="/register" class="block bg-blue-500 text-white px-3 py-2 rounded-md hover:bg-blue-600">
                Register
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 px-4">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from './composables/useAuth'

const router = useRouter()
const { isLoggedIn, logout } = useAuth()
const mobileMenuOpen = ref(false)

const handleLogout = () => {
  logout()
  router.push('/login')
  mobileMenuOpen.value = false
}
</script>