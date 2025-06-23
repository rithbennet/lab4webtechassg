const API_BASE_URL = 'http://localhost:3000/index.php'

// Generic fetch wrapper
async function apiRequest(action, params = {}, options = {}) {
    const url = `${API_BASE_URL}?action=${action}${Object.keys(params)
        .map(key => `&${key}=${encodeURIComponent(params[key])}`)
        .join('')
        }`

    const config = {
        headers: {
            'Accept': 'application/json',
            'Origin': window.location.origin,
            ...(options.method && options.method !== 'GET' ? { 'Content-Type': 'application/json' } : {}),
            ...options.headers,
        },
        mode: 'cors',
        credentials: 'omit',
        cache: 'no-cache',
        ...options,
    };

    try {
        const response = await fetch(url, config)
        const responseText = await response.text();

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}, body: ${responseText}`)
        }

        try {
            const data = JSON.parse(responseText);
            // Log the response structure for debugging
            console.log('API Response structure:', {
                isArray: Array.isArray(data),
                type: typeof data,
                value: data
            });
            return data;
        } catch (parseError) {
            console.error('JSON Parse Error:', {
                error: parseError,
                responseText: responseText.substring(0, 100) + '...' // Show first 100 chars
            });
            throw new Error(`Invalid JSON response: ${responseText}`);
        }
    } catch (error) {
        console.error('API request failed:', error)

        // Handle different types of errors
        if (response && response.status === 0) {
            throw new Error(`CORS Error: Add these headers to your PHP file:
            
            header("Access-Control-Allow-Origin: http://localhost:5173");
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
            header("Access-Control-Allow-Headers: Origin, Content-Type, Accept");
            
            And make sure they're added BEFORE any output or echo statements.
            `)
        }
        if (error.message.includes('Failed to fetch') || error.message.includes('net::ERR_CONNECTION_REFUSED')) {
            throw new Error(`Server connection failed. Make sure:
            1. PHP server is running on port 3000
            2. Try this URL in your browser: ${url}
            3. Check for any PHP errors in the server console`)
        }
        throw error
    }
}

// User API
export const userApi = {
    // Get all users (for login validation)
    getAll: () => apiRequest('getUsers'),

    // Create new user (registration)
    create: (userData) => apiRequest('createUser', userData),

    // Get user by ID
    getById: (id) => apiRequest('getUserById', { id }),

    // Update user (full update)
    update: (id, userData) => apiRequest('updateUser', { id, ...userData }),

    // Patch user (partial update)
    patch: (id, userData) => apiRequest('patchUser', { id, ...userData }),

    // Delete user
    delete: (id) => apiRequest('deleteUser', { id }),
}

// Product API
export const productApi = {
    // Get all products
    getAll: () => apiRequest('getProducts'),

    // Get product by ID
    getById: (id) => apiRequest('getProductById', { id }),

    // Create new product
    create: (productData) => apiRequest('createProduct', productData),

    // Update product
    update: (id, productData) => apiRequest('updateProduct', { id, ...productData }),

    // Patch product
    patch: (id, productData) => apiRequest('patchProduct', { id, ...productData }),

    // Delete product
    delete: (id) => apiRequest('deleteProduct', { id }),
}

// Order API
export const orderApi = {
    // Get all orders
    getAll: async () => {
        const response = await apiRequest('getOrders');
        // Ensure we always return an array
        return Array.isArray(response) ? response : [];
    },

    // Get order by ID
    getById: (id) => apiRequest('getOrderById', { id }),

    // Create new order
    create: (orderData) => apiRequest('createOrder', orderData),

    // Update order
    update: (id, orderData) => apiRequest('updateOrder', { id, ...orderData }),

    // Patch order
    patch: (id, orderData) => apiRequest('patchOrder', { id, ...orderData }),

    // Delete order
    delete: (id) => apiRequest('deleteOrder', { id }),
}
