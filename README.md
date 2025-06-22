# WebTech Lab - Product Management System

A full-stack web application built with Vue.js frontend and JSON Server backend, demonstrating complete CRUD operations for products, users, and orders management.

## 🚀 Quick Start

### Prerequisites

- Node.js (v14 or higher)
- npm
- json-server (will be installed automatically by the setup script)

### Setup

1. **Option 1: Automated Setup**

```bash
chmod +x setup.sh
./setup.sh
```

2. **Option 2: Manual Setup**

```bash
# Install json-server globally (if not already installed)
npm install -g json-server

# Setup frontend
cd frontend
npm install --legacy-peer-deps
cd ..
```

3. Start the backend server:

```bash
cd backend
json-server --watch db.json
```

4. Start the frontend development server (in a new terminal):

```bash
cd frontend
npm run dev
```

5. Open your browser:
   - Frontend: http://localhost:5173 (Vite default) or http://localhost:8080
   - Backend API: http://localhost:3000

## 📁 Project Structure

```
webtechlabphp/
├── backend/
│   └── db.json                 # JSON database
├── frontend/
│   ├── src/
│   │   ├── components/
│   │   │   ├── products/      # Product-specific components
│   │   │   └── ui/            # UI components (Button, Card, etc.)
│   │   ├── services/          # API service functions
│   │   ├── stores/            # State management
│   │   ├── views/             # Page components
│   │   ├── router/            # Vue Router config
│   │   └── assets/            # Static assets
│   ├── package.json
│   └── README.md
├── setup.sh                   # Automated setup script
└── README.md                  # This file
```

## 🎯 Features

### User Management

- **View Users**: Display list of users with their information
- **Role Display**: Show user roles (admin, sales)

### Product Management (CRUD)

- ✅ **CREATE**: Add new products with name and price
- ✅ **READ**: View all products in a responsive table
- ✅ **UPDATE**: Edit existing product details
- ✅ **DELETE**: Remove products with confirmation

### Order Management

- ✅ **READ**: View all orders with user and product details
- ✅ **STATUS TRACKING**: Display order status (pending, shipped)

## 🛠 API Endpoints

### Users

- `GET /users` - Get all users
- `GET /users/:id` - Get specific user

### Products

- `GET /products` - Get all products
- `GET /products/:id` - Get specific product
- `POST /products` - Create a new product
- `PUT /products/:id` - Update a product
- `DELETE /products/:id` - Delete a product

### Orders

- `GET /orders` - Get all orders
- `GET /orders/:id` - Get specific order

## 💻 Technologies Used

- **Frontend**: Vue 3, Vue Router, TypeScript, Tailwind CSS
- **Backend**: JSON Server
- **State Management**: Vue's Composition API with reactive refs
- **HTTP Client**: Native fetch API

## 🚀 Deployment

### Frontend (Production Build)

```bash
cd frontend
npm run build
# Deploy the dist/ folder to your web server
```

### Backend (Production)

For production, replace JSON Server with a proper database and backend framework like:

- Node.js + Express + MongoDB
- PHP + Laravel + MySQL
- Python + Django + PostgreSQL

## 🔧 Troubleshooting

### Common Issues and Solutions

1. **npm dependency conflicts**:

```bash
cd frontend
rm -rf node_modules package-lock.json
npm install --legacy-peer-deps
```

2. **JSON Server not found**:

```bash
npm install -g json-server
```

3. **Port already in use**:

   - Backend: Use `json-server --watch db.json --port 3001`
   - Frontend: The dev server will automatically find an available port

4. **CORS issues**:

   - JSON Server automatically handles CORS for development
   - For production, configure proper CORS headers

5. **Vue CLI installation issues**:

```bash
npm install -g @vue/cli@latest
```

### Manual Installation Steps

If the automated setup fails, follow these steps:

1. **Backend Setup**:

```bash
cd backend
# No installation needed - just ensure json-server is globally installed
```

2. **Frontend Setup**:

```bash
cd frontend
npm install --legacy-peer-deps
# If that fails, try:
npm install --force
```

3. **Alternative Package Manager**:

```bash
# Using Yarn instead of npm
cd frontend
yarn install
yarn serve
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is for educational purposes. Feel free to use and modify as needed.

---
