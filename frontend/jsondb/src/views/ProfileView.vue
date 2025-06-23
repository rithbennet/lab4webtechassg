<template>
  <div class="max-w-4xl mx-auto">
    <div v-if="loggedInUser" class="bg-white shadow rounded-lg">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-gray-200">
        <h1 class="text-2xl font-bold text-gray-900">User Profile</h1>
      </div>

      <!-- Profile Content -->
      <div class="px-6 py-6">
        <div class="grid md:grid-cols-2 gap-8">
          <!-- Profile Information -->
          <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Profile Information</h2>
            
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Full Name</label>
                <div class="mt-1 p-3 bg-gray-50 border border-gray-200 rounded-md">
                  {{ loggedInUser.name }}
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Email Address</label>
                <div class="mt-1 p-3 bg-gray-50 border border-gray-200 rounded-md">
                  {{ loggedInUser.email }}
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Role</label>
                <div class="mt-1 p-3 bg-gray-50 border border-gray-200 rounded-md">
                  <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                        :class="loggedInUser.role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'">
                    {{ loggedInUser.role || 'customer' }}
                  </span>
                </div>
              </div>

              <div v-if="loggedInUser.createdAt">
                <label class="block text-sm font-medium text-gray-700">Member Since</label>
                <div class="mt-1 p-3 bg-gray-50 border border-gray-200 rounded-md">
                  {{ new Date(loggedInUser.createdAt).toLocaleDateString() }}
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
            
            <div class="space-y-3">
              <router-link 
                to="/products" 
                class="block w-full bg-blue-600 text-white text-center px-4 py-3 rounded-md hover:bg-blue-700 transition duration-200"
              >
                Browse Products
              </router-link>

              <router-link 
                to="/orders" 
                class="block w-full bg-green-600 text-white text-center px-4 py-3 rounded-md hover:bg-green-700 transition duration-200"
              >
                View My Orders
              </router-link>

              <router-link 
                v-if="loggedInUser.role === 'admin'" 
                to="/products/new" 
                class="block w-full bg-purple-600 text-white text-center px-4 py-3 rounded-md hover:bg-purple-700 transition duration-200"
              >
                Add New Product
              </router-link>

              <button 
                @click="handleLogout"
                class="block w-full bg-red-600 text-white text-center px-4 py-3 rounded-md hover:bg-red-700 transition duration-200"
              >
                Logout
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Not logged in state -->
    <div v-else class="text-center py-12">
      <div class="bg-white shadow rounded-lg p-8">
        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Not Logged In</h2>
        <p class="text-gray-600 mb-6">Please log in to view your profile</p>
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
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useAuth } from '../composables/useAuth'

const router = useRouter()
const { loggedInUser, logout } = useAuth()

const handleLogout = () => {
  logout()
  router.push('/login')
}
</script>
