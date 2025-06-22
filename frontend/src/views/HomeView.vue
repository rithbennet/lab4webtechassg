<template>
  <div class="min-h-screen">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white">
      <div class="max-w-7xl mx-auto px-4 py-20">
        <div class="text-center">
          <h1 class="text-4xl md:text-6xl font-bold mb-6">
            Welcome to WebTech Store
          </h1>
          <p class="text-xl md:text-2xl mb-8 opacity-90">
            Your one-stop shop for amazing products
          </p>
          <div class="space-x-4">
            <router-link 
              to="/products" 
              class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition duration-300"
            >
              Browse Products
            </router-link>
            <router-link 
              v-if="!isLoggedIn"
              to="/register" 
              class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transition duration-300"
            >
              Get Started
            </router-link>
          </div>
        </div>
      </div>
    </div>

    

    <!-- CTA Section -->
    <div class="bg-white py-20">
      <div class="max-w-4xl mx-auto text-center px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
          Ready to Get Started?
        </h2>
        <p class="text-xl text-gray-600 mb-8">
          Join thousands of satisfied customers today
        </p>
        <div class="space-x-4">
          <router-link 
            v-if="!isLoggedIn"
            to="/register" 
            class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition duration-300"
          >
            Create Account
          </router-link>
          <router-link 
            to="/products" 
            class="border-2 border-blue-600 text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-blue-600 hover:text-white transition duration-300"
          >
            View Products
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useAuth } from '../composables/useAuth'

const { isLoggedIn } = useAuth()
</script>
