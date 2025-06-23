const API_BASE_URL = 'http://localhost:8088'

// Generic fetch wrapper
async function apiRequest(endpoint, options = {}) {
    const url = `${API_BASE_URL}${endpoint}`
    const config = {
        headers: {
            'Content-Type': 'application/json',
            ...options.headers,
        },
        ...options,
    }

    try {
        const response = await fetch(url, config)

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`)
        }

        return await response.json()
    } catch (error) {
        console.error('API request failed:', error)
        throw error
    }
}

// User API
export const userApi = {
    // Get all users (for login validation)
    getAll: () => apiRequest('/users'),

    // Create new user (registration)
    create: (userData) => apiRequest('/users', {
        method: 'POST',
        body: JSON.stringify(userData),
    }),

    // Get user by ID
    getById: (id) => apiRequest(`/users/${id}`),

    // Update user
    update: (id, userData) => apiRequest(`/users/${id}`, {
        method: 'PUT',
        body: JSON.stringify(userData),
    }),

    // Delete user
    delete: (id) => apiRequest(`/users/${id}`, {
        method: 'DELETE',
    }),
}

// Product API
export const productApi = {
    // Get all products
    getAll: () => apiRequest('/products'),

    // Get product by ID
    getById: (id) => apiRequest(`/products/${id}`),

    // Create new product
    create: (productData) => apiRequest('/products', {
        method: 'POST',
        body: JSON.stringify(productData),
    }),

    // Update product
    update: (id, productData) => apiRequest(`/products/${id}`, {
        method: 'PUT',
        body: JSON.stringify(productData),
    }),

    // Delete product
    delete: (id) => apiRequest(`/products/${id}`, {
        method: 'DELETE',
    }),
}

// Order API
export const orderApi = {
    // Get all orders
    getAll: () => apiRequest('/orders'),

    // Get order by ID
    getById: (id) => apiRequest(`/orders/${id}`),

    // Create new order
    create: (orderData) => apiRequest('/orders', {
        method: 'POST',
        body: JSON.stringify(orderData),
    }),

    // Update order
    update: (id, orderData) => apiRequest(`/orders/${id}`, {
        method: 'PUT',
        body: JSON.stringify(orderData),
    }),

    // Delete order
    delete: (id) => apiRequest(`/orders/${id}`, {
        method: 'DELETE',
    }),
}
