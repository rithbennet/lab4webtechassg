#!/bin/bash

# WebTech Lab Setup Script
echo "🚀 Setting up WebTech Lab Project..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if Node.js is installed
if ! command -v node &> /dev/null; then
    echo -e "${RED}❌ Node.js is not installed. Please install Node.js first.${NC}"
    exit 1
fi

# Check if npm is installed
if ! command -v npm &> /dev/null; then
    echo -e "${RED}❌ npm is not installed. Please install npm first.${NC}"
    exit 1
fi

# Check if json-server is installed globally
if ! command -v json-server &> /dev/null; then
    echo -e "${YELLOW}⚠️  json-server not found globally. Installing...${NC}"
    npm install -g json-server
fi

echo -e "${GREEN}✅ Prerequisites check complete${NC}"

# Setup Backend
echo -e "${YELLOW}📦 Setting up backend...${NC}"
cd backend
echo -e "${GREEN}✅ Backend setup complete${NC}"

# Setup Frontend
echo -e "${YELLOW}📦 Setting up frontend...${NC}"
cd ../frontend/frontend

# Install dependencies
echo -e "${YELLOW}🔧 Installing frontend dependencies...${NC}"
npm install
echo -e "${GREEN}✅ Frontend dependencies installed${NC}"

# Create start script for the project
cd ../..
echo -e "${YELLOW}📝 Creating start script...${NC}"

cat > start.sh << 'EOF'
#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Start the JSON Server (Backend)
echo -e "${YELLOW}🚀 Starting JSON Server (Backend)...${NC}"
cd backend
json-server --watch db.json &
BACKEND_PID=$!
echo -e "${GREEN}✅ JSON Server started on http://localhost:3000${NC}"

# Start the Vue.js frontend
echo -e "${YELLOW}🚀 Starting Vue.js Frontend...${NC}"
cd ../frontend/frontend
npm run dev &
FRONTEND_PID=$!
echo -e "${GREEN}✅ Frontend started${NC}"

# Function to handle script termination
function cleanup {
  echo -e "${YELLOW}⏹️  Stopping all services...${NC}"
  kill $BACKEND_PID
  kill $FRONTEND_PID
  echo -e "${GREEN}✅ All services stopped${NC}"
  exit 0
}

# Set up trap to catch termination signal
trap cleanup SIGINT

echo -e "${GREEN}✅ All services are running!${NC}"
echo -e "${YELLOW}📱 Access the application at the URL shown above${NC}"
echo -e "${YELLOW}🛑 Press Ctrl+C to stop all services${NC}"

# Wait forever (until Ctrl+C)
wait
EOF

chmod +x start.sh
echo -e "${GREEN}✅ Start script created${NC}"
echo -e "${GREEN}✅ Setup complete. Run ./start.sh to start the application${NC}"

# Install dependencies with legacy peer deps to resolve conflicts
echo -e "${YELLOW}📥 Installing frontend dependencies...${NC}"
npm install --legacy-peer-deps

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Frontend setup complete${NC}"
else
    echo -e "${RED}❌ Frontend setup failed. Trying alternative installation...${NC}"
    npm install --force
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ Frontend setup complete (with --force)${NC}"
    else
        echo -e "${RED}❌ Frontend setup failed. Please check the error messages above.${NC}"
    fi
fi

# Go back to root directory
cd ..

echo -e "${GREEN}🎉 Setup complete!${NC}"
echo ""
echo -e "${YELLOW}To start the application:${NC}"
echo ""
echo -e "${YELLOW}1. Start the backend server:${NC}"
echo "   cd backend"
echo "   json-server --watch db.json"
echo ""
echo -e "${YELLOW}2. In a new terminal, start the frontend:${NC}"
echo "   cd frontend"
echo "   npm run serve"
echo ""
echo -e "${YELLOW}3. Open your browser and navigate to:${NC}"
echo "   Backend API: http://localhost:3000"
echo "   Frontend App: http://localhost:8080"
echo ""
echo -e "${GREEN}Happy coding! 🚀${NC}"
